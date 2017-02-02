package com.paniya.icareu;

import android.app.ListActivity;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.TextView;
import android.app.ListActivity;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Toast;


public class menu extends ListActivity {

    ListView lv;
    public static String selectedElder;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);

        final TextView welcomeMessage = (TextView)findViewById(R.id.tvwelcomemsg);

        Intent intent = getIntent();
        final Intent nextIntent = new Intent(this,pick.class);
        String ID = intent.getStringExtra("UserID");

        String message = "Welcome" +" " + ID;
        welcomeMessage.setText(message);

        Elder el = new Elder();
        this.setListAdapter(new ArrayAdapter<String>(
                this, R.layout.my_list,
                R.id.Itemname,el.getElderNames(login.Elders)));

        ListView lv = getListView();
        lv.setOnItemClickListener(new AdapterView.OnItemClickListener()
        {
            @Override
            public void onItemClick(AdapterView<?> adapter, View v, int position,
                                    long arg3)
            {
                String value = (String)adapter.getItemAtPosition(position);
                Toast.makeText(menu.this, "You clicked "+value, Toast.LENGTH_LONG).show();
                selectedElder = login.Elders.get(position).ID;
                startActivity(nextIntent);
            }
        });
    }


}

