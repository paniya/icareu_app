package com.paniya.icareu;

import android.app.AlertDialog;
import android.content.Context;
import android.content.Intent;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;


public class getElders extends AsyncTask<String, Void, String> {
    Context context;
    AlertDialog alertD;
    String[] arr;

    getElders(Context ctx){
        context =  ctx;
    }

    @Override
    protected String doInBackground(String... params) {
        String result ="";
        String login_url = "http://icareu.azurewebsites.net/mobileApp.php";
        try {
            URL url = new URL(login_url);
            HttpURLConnection httpURLConnection = (HttpURLConnection)url.openConnection();
            httpURLConnection.setRequestMethod("POST");
            httpURLConnection.setDoOutput(true);
            httpURLConnection.setDoInput(true);
            OutputStream out = httpURLConnection.getOutputStream();
            BufferedWriter bufferW = new BufferedWriter(new OutputStreamWriter(out,"UTF-8"));
            String post_dat = URLEncoder.encode("func","UTF-8")+"="+URLEncoder.encode("getElders","UTF-8")+"&"+URLEncoder.encode("Debg","UTF-8")+"="+URLEncoder.encode("true","UTF-8");
            bufferW.write(post_dat);
            bufferW.flush();
            bufferW.close();
            out.close();

            InputStream input = httpURLConnection.getInputStream();
            BufferedReader bufferR = new BufferedReader(new InputStreamReader(input,"iso-8859-1"));

            String line;

            while((line = bufferR.readLine()) != null){
                result += line;
            }
            bufferR.close();
            input.close();
            httpURLConnection.disconnect();

            return result;

        } catch (MalformedURLException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }

        return null;
    }

    @Override
    protected void onPreExecute() {
        alertD =  new AlertDialog.Builder(context).create();
        alertD.setTitle("Login Status");
    }

    @Override
    protected void onPostExecute(final String result) {

        if(result==null){
            alertD.setMessage("error");
            alertD.show();
            return;
        }
        else{
            String ret = result.trim();
            if (ret.length() > 0){
                Log.v("got elders",ret);

                String[] elders = ret.split("#");
                int i;
                for (i=0;i<elders.length;i++){
                    String[] info = elders[i].split("%");
                    Elder el = new Elder(info[0],info[1]);
                    Log.v("name",el.name);
                    Log.v("id", el.image);
                    login.Elders.add(el);
                }
            }

            else{
                //no elders to show
                alertD.setMessage("no elders to show");
                alertD.show();
            }
            Intent intent = new Intent(context, menu.class);
            intent.putExtra("UserID", login.userID);
            context.startActivity(intent);
        }
    }



    @Override
    protected void onProgressUpdate(Void... values) {
        super.onProgressUpdate(values);
    }
}
