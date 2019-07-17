package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.annotation.Nullable;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DriverLoginResponse;
import com.example.eco_rest_retro_mysql.R;

import org.jetbrains.annotations.NotNull;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DriverLoginActivity extends AppCompatActivity implements View.OnClickListener{

    private EditText editTextUsername, editTextPassword;
    public static String currentUser;
    public static int currentUserId;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_driver_login);

        editTextUsername = findViewById(R.id.editTextUsername);
        editTextPassword = findViewById(R.id.editTextPassword);

        findViewById(R.id.textViewRegister).setOnClickListener(this);
        findViewById(R.id.buttonLogin).setOnClickListener(this);
    }

    private void driverLogin()
    {
        String username = editTextUsername.getText().toString().trim();
        String password = editTextPassword.getText().toString().trim();

        if(username.isEmpty())
        {
            editTextUsername.setError("Username is required");
            editTextUsername.requestFocus();
            return;
        }

        if(password.isEmpty())
        {
            editTextPassword.setError("Password is required");
            editTextPassword.requestFocus();
            return;
        }


        Call<DriverLoginResponse> call = RetrofitClient.getmInstance().getApi().driverLogin(username,password);

        call.enqueue(new Callback<DriverLoginResponse>() {
            @NotNull
            @Override
            public void onResponse(Call<DriverLoginResponse> call,  Response<DriverLoginResponse> response) {

                if(!response.body().isError())
                {
                    currentUser = response.body().getDriver().getUsername();
                    currentUserId = response.body().getDriver().getDriver_id();
                    Toast.makeText(DriverLoginActivity.this, response.body().getMessage() + " Welcome: " + currentUser, Toast.LENGTH_LONG).show();
                    startActivity(new Intent( DriverLoginActivity.this , DriverMapsActivity.class ));
                }else
                    {
                        Toast.makeText(DriverLoginActivity.this, response.body().getMessage(), Toast.LENGTH_LONG).show();
                    }
            }

            @Override
            public void onFailure(Call<DriverLoginResponse> call, Throwable t) {

            }
        });

    }

    @Override
    public void onClick(View v) {
        switch (v.getId())
        {
            case R.id.buttonLogin:
                driverLogin();
                break;
            case R.id.textViewRegister:
                startActivity(new Intent(this , DriverRegisterActivity.class));
                break;

        }
    }

    public static String getCurrentDriverUsername()
    {
        return currentUser;
    }

    public static int getCurrentDriverId()
    {
        return currentUserId;
    }


}
