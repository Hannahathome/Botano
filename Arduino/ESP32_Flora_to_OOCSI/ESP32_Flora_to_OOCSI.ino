/**
   The following code is based on the code from: https://github.com/sidddy/flora.
   and uses the OOCSI library and example codes from: https://github.com/iddi/oocsi-esp

   Edited and adapted for Botano Project by:
   Hannah van Iterson, Master student Industrial Design at the TU/e

   With help and tips from:
   Bas van Rossem, HBO student IT at the UU
   Michiel Molenaar, Industrial Design student at the TU/e
   Thimo Ferede, Pre-master at the TU/e, HBO student IT at Fontys

   2020
*/

//--- LIBRARIES -----------------------//
#include "BLEDevice.h"
#include <OOCSI.h>


//--- INTERNET LOGIN ------------------//
//Wifi address
const char*   ssid       = "Add SSID here";
const char*   password   = "Add password here";

//--- CONNECT TO OOCSI ----------------//
OOCSI oocsi = OOCSI();
const char* OOCSIName = "Add a unique handle here";           // name for connecting with OOCSI (unique handle)
const char* hostserver = "Add the address of OOCSI server";   // put the adress of your OOCSI server here, can be URL or IP address string


//--- SLEEP & HIBERNATION CONSTANTS ---//
#define SLEEP_DURATION 30 * 60         // sleep between to runs in seconds
#define EMERGENCY_HIBERNATE 3 * 60     // emergency hibernate countdown in seconds
#define BATTERY_INTERVAL 6             // how often should the battery be read - in run count
#define RETRY 3                        // how often should a device be retried in a run when something fails

TaskHandle_t hibernateTaskHandle = NULL;


//--- DEVICES -------------------------//
// array of devices' MAC addressess
char* FLORA_DEVICES[] = {"Add MAC address of flora device here" };

// device count
static int deviceCount = sizeof FLORA_DEVICES / sizeof FLORA_DEVICES[0];

// boot count used to check if battery status should be read
RTC_DATA_ATTR int bootCount = 0;


//--- REMOTE SERVICE ------------------//
// the remote service we wish to connect to
static BLEUUID serviceUUID("00001204-0000-1000-8000-00805f9b34fb");

// the characteristic of the remote service we are interested in
static BLEUUID uuid_version_battery("00001a02-0000-1000-8000-00805f9b34fb");
static BLEUUID uuid_sensor_data("00001a01-0000-1000-8000-00805f9b34fb");
static BLEUUID uuid_write_mode("00001a00-0000-1000-8000-00805f9b34fb");


//--- FLORA SENSOR DATA --------------//
typedef struct floraData {
  float temperature;
  int moisture;
  int light;
  int conductivity;
  int battery;
  bool success;
} floraData;

struct floraData FData;


//--- BLUETOOTH CLIENT --------------//
// connecting to the flora device
BLEClient* getFloraClient(BLEAddress floraAddress) {
  BLEClient* floraClient = BLEDevice::createClient();

  if (!floraClient->connect(floraAddress)) {
    Serial.println("- Connection failed, skipping");
    return nullptr;
  }

  Serial.println("- Connection successful");
  return floraClient;
}

// connecting tot the data service of that device
BLERemoteService* getFloraService(BLEClient* floraClient) {
  BLERemoteService* floraService = nullptr;

  try {
    floraService = floraClient->getService(serviceUUID);
  }
  catch (...) {
    // something went wrong
  }
  if (floraService == nullptr) {
    Serial.println("- Failed to find data service");
  }
  else {
    Serial.println("- Found data service");
  }
  return floraService;
}


//--- BOOLEANS FOR SETTING FLORA SENSOR TO READ MODE & EXTRACTING DATA ---//
bool forceFloraServiceDataMode(BLERemoteService* floraService) {
  BLERemoteCharacteristic* floraCharacteristic;

  // get device mode 'characteristic', this needs to be changed to 'read data'
  Serial.println("- Force device in data mode");
  floraCharacteristic = nullptr;
  try {
    floraCharacteristic = floraService->getCharacteristic(uuid_write_mode);
  }
  catch (...) {
    // something went wrong
  }
  if (floraCharacteristic == nullptr) {
    Serial.println("-- Failed, skipping device");
    return false;
  }

  // write the data
  uint8_t buf[2] = {0xA0, 0x1F};
  floraCharacteristic->writeValue(buf, 2, true);

  delay(500);
  return true;
}

bool readFloraDataCharacteristic(BLERemoteService* floraService, struct floraData* retData) {
  BLERemoteCharacteristic* floraCharacteristic = nullptr;

  // get the main device data characteristic
  Serial.println("- Access characteristic from device");
  try {
    floraCharacteristic = floraService->getCharacteristic(uuid_sensor_data);
  }
  catch (...) {
    // something went wrong
  }
  if (floraCharacteristic == nullptr) {
    Serial.println("-- Failed, skipping device");
    return false;
  }

  // read characteristic value
  Serial.println("- Read value from characteristic");
  std::string value;
  try {
    value = floraCharacteristic->readValue();
  }
  catch (...) {
    // something went wrong
    Serial.println("-- Failed, skipping device");
    return false;
  }
  const char *val = value.c_str();

  // reading temperature
  int16_t* temp_raw = (int16_t*)val;
  float temperature = (*temp_raw) / ((float)10.0);
  Serial.print("-- Temperature: ");
  Serial.println(temperature);
  Serial.println(retData->temperature);

  // reading moisture
  int moisture = val[7];
  Serial.print("-- Moisture: ");
  Serial.println(moisture);

  // reading light
  int light = val[3] + val[4] * 256;
  Serial.print("-- Light: ");
  Serial.println(light);

  // reading conductivity
  int conductivity = val[8] + val[9] * 256;
  Serial.print("-- Conductivity: ");
  Serial.println(conductivity);

  // catch strange values
  if ((temperature > 200) || (temperature < -100)) {
    Serial.println("-- Unreasonable values received, skip publish");
    return false;
  }

  //save data in the retData struct
  retData->temperature = temperature;
  retData->moisture = moisture;
  retData->light = light;
  retData->conductivity = conductivity;

  //save data in FData for packaging and sending to OOCSI
  FData.temperature = temperature;
  FData.moisture = moisture;
  FData.light = light;
  FData.conductivity = conductivity;

  return true;
}

//--- BOOLEAN FOR BATTERY STATUS ---//
bool readFloraBatteryCharacteristic(BLERemoteService* floraService, struct floraData* retData) {
  BLERemoteCharacteristic* floraCharacteristic = nullptr;

  // get the device battery characteristic
  Serial.println("- Access battery characteristic from device");
  try {
    floraCharacteristic = floraService->getCharacteristic(uuid_version_battery);
  }
  catch (...) {
    // something went wrong
  }
  if (floraCharacteristic == nullptr) {
    Serial.println("-- Failed, skipping battery level");
    return false;
  }

  // read characteristic value
  Serial.println("- Read value from characteristic");
  std::string value;
  try {
    value = floraCharacteristic->readValue();
  }
  catch (...) {
    // something went wrong
    Serial.println("-- Failed, skipping battery level");
    return false;
  }
  const char *val2 = value.c_str();
  int battery = val2[0];

  Serial.print("-- Battery: ");
  Serial.println(battery);
  retData->battery = battery;

  return true;
}

bool processFloraService(BLERemoteService* floraService, bool readBattery, struct floraData* retData) {
  // set device in data mode
  if (!forceFloraServiceDataMode(floraService)) {
    return false;
  }

  bool dataSuccess = readFloraDataCharacteristic(floraService, retData);

  bool batterySuccess = true;
  if (readBattery) {
    batterySuccess = readFloraBatteryCharacteristic(floraService, retData);
  }

  retData->success = dataSuccess && batterySuccess;
  return retData->success;
}


//--- BLUETOOTH CONNECTION ---//
bool processFloraDevice(BLEAddress floraAddress, bool getBattery, int tryCount, struct floraData* retData) {
  Serial.print("Processing Flora device at ");
  Serial.print(floraAddress.toString().c_str());
  Serial.print(" (try ");
  Serial.print(tryCount);
  Serial.println(")");

  // connect to flora ble server
  BLEClient* floraClient = getFloraClient(floraAddress);
  if (floraClient == nullptr) {
    return false;
  }

  // connect data service
  BLERemoteService* floraService = getFloraService(floraClient);
  if (floraService == nullptr) {
    floraClient->disconnect();
    return false;
  }

  // process devices data
  bool success = processFloraService(floraService, getBattery, retData);

  // disconnect from device
  floraClient->disconnect();

  return success;
}

//-------------------------------------------------------------------------------------------------------------------------------------//

void hibernate() {
  esp_sleep_enable_timer_wakeup(SLEEP_DURATION * 1000000ll);
  Serial.println("Going to sleep now.");
  delay(100);
  esp_deep_sleep_start();
}

void delayedHibernate(void *parameter) {
  delay(EMERGENCY_HIBERNATE * 1000); // delay for five minutes
  Serial.println("Something got stuck, entering emergency hibernate...");
  hibernate();
}

void processOOCSI() {
  // don't do anything; we are sending data only
  // however, keep the loop. it is needed for the oocsi.connect in the void setup 
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  oocsi.connect(OOCSIName, hostserver, ssid, password, processOOCSI);
  bool readBattery = ((bootCount % BATTERY_INTERVAL) == 0);   // check if battery status should be read - based on boot count

  //--- BOOTCOUNT ---//
  Serial.print("Boot number: ");
  Serial.println(bootCount);
  bootCount++; 

  // create a hibernate task in case something gets stuck
  xTaskCreate(delayedHibernate, "hibernate", 4096, NULL, 1, &hibernateTaskHandle);

  //--- BLUETOOTH ---//
  Serial.println("Initialize BLE client...");
  BLEDevice::init("");
  BLEDevice::setPower(ESP_PWR_LVL_P7);

  struct floraData* deviceData = (struct floraData*)malloc(deviceCount * sizeof(struct floraData));


  //--- DEVICES ---//
  for (;;) {
    // process devices
    for (int i = 0; i < deviceCount; i++) {
      int tryCount = 0;
      char* deviceMacAddress = FLORA_DEVICES[i];
      BLEAddress floraAddress(deviceMacAddress);

      while (tryCount < RETRY) {
        tryCount++;
        if (processFloraDevice(floraAddress, readBattery, tryCount, &(deviceData[i]))) {
          break;
        }
        delay(1000);
      }
      delay(1500);
      oocsi.newMessage("Your Channel Name");
      oocsi.addFloat("temperature", FData.temperature);
      oocsi.addInt("light_intensity", FData.light);
      oocsi.addInt("plant_nutrient_s001", FData.conductivity);
      oocsi.addInt("soil_moisture_s001", FData.moisture);
      oocsi.sendMessage();
      oocsi.printSendMessage();
      oocsi.check();
    }
    delay(10000);
  }
  delay(10000);

  // delete emergency hibernate task
  vTaskDelete(hibernateTaskHandle);

  // go to sleep now
  hibernate();
}

void loop() {
  /// we're not doing anything in the loop, only on device wakeup
  delay(10000);
}
