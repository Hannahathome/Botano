# Botano
Code for the project Botano, a first year masters project at the TU/e.

Botano is a family of products, which helps users take care of their edible plants and inspires them to cook with them by recommending recipes.
[Learn more about Botano](https://demoday.id.tue.nl/projects/4eRXnWmj38)

## Files in this repository
- Arduino code for ESP32 (for connecting the ESP to the OOCSI network)
- Python code (for creating ingredients vectors; for recommending recipes based on the entered plants and selected filters (via OOCSI HTML)) 
- HTML, PHP & JavaScript (for creating a website to display the dataflows to and from OOCSI) 

## OOCSI
[OOCSI](https://github.com/iddi/oocsi) is a opensource prototyping tool which supports connected prototyping and communication between groups of products across platforms.
In this repository, OOCSI is used as a platform through which we communicate data between the different parts of Botano. 
