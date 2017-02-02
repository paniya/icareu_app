package com.paniya.icareu;

import com.android.volley.Response;
import com.android.volley.toolbox.StringRequest;

import java.util.HashMap;
import java.util.Map;

/**
 * Created by Praneeth Perera on 12/8/2016.
 */

public class makeappo extends StringRequest{

    private static final String MAKE_APPOI_URL = "http://icareu.azurewebsites.net/android/makeapp.php";
    private Map<String, String> params;

    public makeappo(String PhysicanID, String AppointmentDate, String Reason, String Description, Response.Listener<String> listener){
        super(Method.POST, MAKE_APPOI_URL, listener, null);
        params = new HashMap<>();
        params.put("PhysicianID", PhysicanID);
        params.put("AppointmentDate", AppointmentDate + "");
        params.put("Reason", Reason);
        params.put("Description", Description);
    }

    @Override
    public Map<String, String> getParams() {
        return params;
    }
}
