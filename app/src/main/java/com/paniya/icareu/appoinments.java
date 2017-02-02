package com.paniya.icareu;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

public class appoinments extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_appoinments);
    }

    public void onclickadd(View view){
        Intent i =new Intent(appoinments.this,makeapp.class);
        startActivity(i);
    }
}
