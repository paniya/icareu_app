package com.paniya.icareu;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import java.util.ArrayList;

public class login extends AppCompatActivity {

    public static ArrayList<Elder> Elders = new ArrayList<Elder>();
    public static String userID;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        final EditText etID = (EditText) findViewById(R.id.etuserid);
        final EditText etpassword = (EditText) findViewById(R.id.etpassword);
        final Button blogin = (Button) findViewById(R.id.blogin);

        blogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final String ID = etID.getText().toString();
                final String password = etpassword.getText().toString();

                userID = ID;
                signIn sI = new signIn(login.this);
                sI.execute(ID,password);
            }
        });
    }
}