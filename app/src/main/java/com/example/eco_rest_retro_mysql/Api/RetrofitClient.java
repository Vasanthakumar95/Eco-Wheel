package com.example.eco_rest_retro_mysql.Api;

import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class RetrofitClient {

                                            //13.58.111.54(aws)192.168.43.201
    private static final String BASE_URL = "http://192.168.43.201/ECOWHEEL/public/";
    private static RetrofitClient mInstance;
    private Retrofit retrofit;


    private RetrofitClient()
    {
        retrofit = new Retrofit.Builder().baseUrl(BASE_URL)
                                         .addConverterFactory(GsonConverterFactory.create())
                                         .build();
    }

    public static synchronized RetrofitClient getmInstance()
    {
        if(mInstance == null)
        {
            mInstance = new RetrofitClient();
        }
        return mInstance;
    }

    public Api getApi()
    {
        return retrofit.create(Api.class);
    }
}
