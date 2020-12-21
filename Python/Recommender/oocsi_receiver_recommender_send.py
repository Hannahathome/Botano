# -*- coding: utf-8 -*-
import time
from oocsi import OOCSI
import pandas as pd
import keyboard

df_rec = pd.read_pickle('new_dataframe.pkl')

plant_list =[]
for i in range(len(df_rec['plants'])):
    temp = df_rec['plants'].loc[i].split(", ")
    
    for j in range(len(temp)):
        plant = temp[j]
        plant_list.append(plant)
unique_plant = list(set(plant_list))
unique_plant = sorted(unique_plant)

def plants_vector(plants):
    for i in range(len(df_rec['plants'])):
        vector_plant = [] 
        tempStr = plants.split(", ")
        for j in range(len(unique_plant)):
            if unique_plant[j] in tempStr:  
                vector_plant.append(1)
            else:
                vector_plant.append(0)
    return vector_plant

    
# Most popular recipes in general
def pos_recipes(plants):
    print("Recepten met ", plants, ":")
    global df_recipes
    df_recipes = pd.DataFrame(columns=['recept', 'popularity'])
    df_recipes.style.hide_index()
    a= plants_vector(plants)
    for e in range(len(df_rec['Plant vector'])):
        b= df_rec['Plant vector'].loc[e]
       # print(e, df_rec['recipe_name'].loc[e])
        count =0
        for i, j in zip(a, b):
            if i == j:
                count = count+ i
                #print(count)
        #print(e, df_rec['recipe_name'].loc[e], count)
        if count>0:
            #print (df_rec['recipe_name'].loc[e], count)
            df_recipes =df_recipes.append({'recept': df_rec['recipe_name'].loc[e], 'popularity': df_rec['popularity'].loc[e]}, ignore_index=True)
    df_recipes= df_recipes.sort_values(by=['popularity'], ascending=False).reset_index(drop=True)


# Personal recommendations    
def pers_recipes(plants):
    print("Persoonlijke aanbevelingen met ", plants, ":")
    global df_personalRec
    df_personalRec = pd.DataFrame(columns=['recept', 'personal_count'])
    df_personalRec.style.hide_index()
    a= plants_vector(plants)
    for e in range(len(df_rec['Plant vector'])):
        b= df_rec['Plant vector'].loc[e]
       # print(e, df_rec['recipe_name'].loc[e])
        count =0
        for i, j in zip(a, b):
            if i == j:
                count = count+ i
                #print(count)
        #print(e, df_rec['recipe_name'].loc[e], count)
        if count>0:
            #print (df_rec['recipe_name'].loc[e], count)
            df_personalRec =df_personalRec.append({'recept': df_rec['recipe_name'].loc[e], 'personal_count': df_rec['personal_count'].loc[e]}, ignore_index=True)
    df_personalRec= df_personalRec.sort_values(by=['personal_count'], ascending=False).reset_index(drop=True)

# Family favorites
def family_recipes(plants):
    print("Familie favorieten met: ", plants, ":")
    global df_famfav
    df_famfav = pd.DataFrame(columns=['recept', 'family_favorites'])
    df_famfav.style.hide_index()
    a= plants_vector(plants)
    for e in range(len(df_rec['Plant vector'])):
        b= df_rec['Plant vector'].loc[e]
       # print(e, df_rec['recipe_name'].loc[e])
        count =0
        for i, j in zip(a, b):
            if i == j:
                count = count+ i
                #print(count)
        #print(e, df_rec['recipe_name'].loc[e], count)
        if count>0:
           # print (df_rec['recipe_name'].loc[e], df_rec["family_favorites"].loc[e]) 
            if (df_rec['family_favorites'].loc[e] ==1):
                #print(df_rec['recipe_name'].loc[e])
                      
                #print (df_rec['recipe_name'].loc[e], count)
                df_famfav =df_famfav.append({'recept': df_rec['recipe_name'].loc[e]}, ignore_index=True)

    
# connect to OOCSI running on a webserver
oocsi = OOCSI('Botano_Recipe_Recommender_8', "oocsi.id.tue.nl")
#oocsi.subscribe('RecipeRecommender', receiveEvent)
ing = oocsi.variable('RecipeRecommender', 'ingredients')
filterVar = oocsi.variable('RecipeRecommender', 'filter')
oldFilter= filterVar.get()
filterVar.set("popular")
ing.set('garlic')
v1= ing.get()

v1= ing.get()


while True:
   # print(v1, ing.get())
  # print(filterVar.get())
   if (v1 != ing.get()) or filterVar.get() != oldFilter:
       if filterVar.get() == "popular":
            pos_recipes(ing.get())
            print(df_recipes['recept'][:10])
            message = {}
            if len(df_recipes['recept']) > 10:
                
                for i in range(10):
                    try:
                        message['Recipe'+ str(i)] = df_recipes['recept'].loc[i]
                    except:
                        print("No recipes")
            else:
                for i in range(len(df_recipes['recept'])):
                     message['Recipe'+ str(i)] = df_recipes['recept'].loc[i]
       if filterVar.get() == "personal":
            pers_recipes(ing.get())
            print(df_personalRec['recept'][:10])
            message = {}
            if len(df_personalRec['recept']) > 10:
                for i in range(10):
                    try:
                        message['Recipe'+ str(i)] = df_personalRec['recept'].loc[i]
                    except:
                        print("No recipes")
            else:
                for i in range(len(df_personalRec['recept'])):
                    message['Recipe'+ str(i)] = df_personalRec['recept'].loc[i]
       if filterVar.get() =="family":
            family_recipes(ing.get())
            print(df_famfav['recept'][:10])
            message = {}
            if len(df_famfav['recept']) >10:
                 
                for i in range(10):
                    try:
                        message['Recipe'+ str(i)] = df_famfav['recept'].loc[i]
                    except:
                        print("No recipes")
            else:
                for i in range(len(df_famfav['recept'])):
                    message['Recipe'+ str(i)] = df_famfav['recept'].loc[i]
     
       
       oocsi.send('RecipeRecommender', message)
       # print(ing.get(), df_recipes['recept'].loc[0])
       v1= ing.get()
       oldFilter= filterVar.get()
       time.sleep(1)
     
   try:
         if keyboard.is_pressed('esc'):
             oocsi.stop()
             break
   except:
        break
    

    
