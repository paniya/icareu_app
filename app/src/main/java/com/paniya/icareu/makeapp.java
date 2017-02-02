package com.paniya.icareu;


import android.content.Intent;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;
import android.view.Menu;
import android.view.MenuItem;
import android.support.v4.app.DialogFragment;

import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import static com.paniya.icareu.R.string.date;

public class makeapp extends AppCompatActivity {
    Spinner spinner;
    ArrayAdapter<CharSequence> adapter;
    Button bt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_makeapp);

        final EditText etdate = (EditText)findViewById(R.id.etdate);
        final EditText etmsg = (EditText)findViewById(R.id.etmsg);

        spinner = (Spinner)findViewById(R.id.spinner);
        adapter = ArrayAdapter.createFromResource(this,R.array.Physician_names,android.R.layout.simple_spinner_item);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_item);
        spinner.setAdapter(adapter);
        spinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                Toast.makeText(getBaseContext(),parent.getItemAtPosition(position)+ " selected",Toast.LENGTH_LONG).show();

            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });

        bt = (Button)findViewById(R.id.btmake);
        bt.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View view){
                final String PhysicianID = spinner.getSelectedItem().toString();
                final String AppointmentDate = etdate.getText().toString();
                final String Reason = etmsg.getText().toString();

                Response.Listener<String> responseListener = new Response.Listener<String>(){
                    @Override
                    public void onResponse(String response) {
                        try {
                            JSONObject jsonResponse = new JSONObject(response);
                            boolean success = jsonResponse.getBoolean("success");

                            if (success){
                                Intent intent = new Intent(makeapp.this, appoinments.class);
                                makeapp.this.startActivity(intent);
                            }else{
                                AlertDialog.Builder builder = new AlertDialog.Builder(makeapp.this);
                                builder.setMessage("Failed")
                                        .setNegativeButton("Retry", null)
                                        .create()
                                        .show();
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                    }
                };

                makeappo makeappo = new makeappo(PhysicianID, AppointmentDate, Reason, responseListener);
                RequestQueue queue = Volley.newRequestQueue(makeapp.this);
                queue.add(makeappo);

                Toast.makeText(getApplicationContext(),"You make Your Appointment", Toast.LENGTH_LONG).show();
            }
        });}

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    public void showDatePickerDialog(View v) {
        DialogFragment newFragment = new DatePickerFragment();
        newFragment.show(getSupportFragmentManager(), "datePicker");
    }
}