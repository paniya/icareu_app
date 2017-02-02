package com.paniya.icareu;

import java.util.ArrayList;

public class Elder {

    String name,ID,image;

    public Elder(){

    }

    public Elder(String name, String ID){
        this.name = name;
        this.ID = ID;
        this.image = "http://icareu.azurewebsites.net/profile/" + ID + ".jpg";
    }

    public String[] getElderNames(ArrayList<Elder> el){
        int i;
        ArrayList<String> elderNameArray = new ArrayList<String>();
        for(i=0;i<el.size();i++){
            elderNameArray.add(el.get(i).name);
        }
        String[] elArr = new String[elderNameArray.size()];
        elArr = elderNameArray.toArray(elArr);
        return elArr;
    }

}
