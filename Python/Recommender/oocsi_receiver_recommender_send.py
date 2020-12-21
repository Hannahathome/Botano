# -*- coding: utf-8 -*-
# Place the 'recipe_data.pkl' in the same folder as this file
# This code works only if connected to an OOCSI channel and receives input ingredients
# Always close the OOCSI connection with esc after using it so you can continue using the same unique_handler


import time
from oocsi import OOCSI
import pandas as pd
import keyboard

df_rec = pd.read_pickle('recipe_data.pkl')

# A list with all the ingredients from the recipes that can be grown in one's garden
plant_list =[]
for i in range(len(df_rec['plants'])):
    temp = df_rec['plants'].loc[i].split(", ")
    
    for j in range(len(temp)):
        plant = temp[j]
        plant_list.append(plant)
unique_plant = list(set(plant_list))
unique_plant = sorted(unique_plant)


# Create a vector to represent which plants are used in that recipe. The sequence is based on the plant_list.
# A 0 is added if the plant is not in the recipe, a 1 if it is in the recipe
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
# Create a dataframe with all the possible recipes with at least one of the entered ingredients
# Sort the dataframe on popularity score to get the most popular recipes
def pos_recipes(plants):
    print("Recepten met ", plants, ":")
    global df_recipes
    df_recipes = pd.DataFrame(columns=['recept', 'popularity'])
    df_recipes.style.hide_index()
    a= plants_vector(plants)
    for e in range(len(df_rec['Plant vector'])):
        b= df_rec['Plant vector'].loc[e]
        # count can be used to see how many of the entered ingredients are used in that recipe
        # currently it is not being used except to see if there is at least 1 plant used
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
oocsi = OOCSI('Your_Unique_Handler', "oocsi.id.tue.nl")
ing = oocsi.variable('RecipeRecommender', 'ingredients')
filterVar = oocsi.variable('RecipeRecommender', 'filter')
oldFilter= filterVar.get()
filterVar.set("popular")
ing.set('garlic')
v1= ing.get()

v1= ing.get()


while True:
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
    

    
