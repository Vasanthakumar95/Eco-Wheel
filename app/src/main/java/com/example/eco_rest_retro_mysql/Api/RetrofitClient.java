package com.example.eco_rest_retro_mysql.Api;

import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class RetrofitClient {

                                            // example = 13.58.111.54
    private static final String BASE_URL = "http://example/ECOWHEEL/public/";
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
