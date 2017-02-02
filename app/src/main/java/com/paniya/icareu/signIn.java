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

public class signIn extends AsyncTask <String, Void, String> {
    Context context;
    AlertDialog alertD;
    String[] arr;

    signIn(Context ctx){

        context =  ctx;
    }

    @Override
    protected String doInBackground(String... params) {
        String result ="";
        String login_url = "http://icareu.azurewebsites.net/login.php";
         try {
                String ID = params[0];
                String pass = params[1];
                URL url = new URL(login_url);
                HttpURLConnection httpURLConnection = (HttpURLConnection)url.openConnection();
                httpURLConnection.setRequestMethod("POST");
                httpURLConnection.setDoOutput(true);
                httpURLConnection.setDoInput(true);
                OutputStream out = httpURLConnection.getOutputStream();
                BufferedWriter bufferW = new BufferedWriter(new OutputStreamWriter(out,"UTF-8"));
                String post_dat = URLEncoder.encode("func","UTF-8")+"="+URLEncoder.encode("login","UTF-8")+"&"+URLEncoder.encode("device","UTF-8")+"="+URLEncoder.encode("mobile","UTF-8")+"&"+URLEncoder.encode("user","UTF-8")+"="+URLEncoder.encode(ID,"UTF-8")+"&"+URLEncoder.encode("pass","UTF-8")+"="+URLEncoder.encode(pass,"UTF-8");
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
        Toast.makeText(context,"Please Wait..", Toast.LENGTH_SHORT).show();

        if(result==null){
            alertD.setMessage("error");
            alertD.show();
            return;
        }
        String ret = result.trim();
        if (ret.length() > 0){
            if ( (ret != "invalid") && (ret != "error") && (ret != "") ){
                if (ret.charAt(0) == '0'){
                        //'admin
                }
                else if (ret.charAt(0) == '2'){
                    //'guardians
                    ////////////////guardianID = ret;
                    //load physicains list in Appointmnets -> appAddNew
                    //-- get defaults--
                    //get elders
                    getElders gE = new getElders(context);
                    gE.execute();
                    }
                }
                else{
                    if(result==null){
                        alertD.setMessage("login failed");
                        alertD.show();
                        return;
                    }
                }
            }
        else{
            if(result==null){
                alertD.setMessage("login failed");
                alertD.show();
                return;
            }
        }

    }


    @Override
    protected void onProgressUpdate(Void... values) {
        super.onProgressUpdate(values);
    }
}
