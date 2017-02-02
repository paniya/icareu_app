package com.paniya.icareu;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

public class pick extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_pick);
    }

    public void onclickapp(View view){
        Intent i =new Intent(pick.this,appoinments.class);
        startActivity(i);
    }

    public void onclickpill(View view){
        Intent i =new Intent(pick.this,pillpod.class);
        startActivity(i);
    }
}
