package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DriverLoginResponse;
import com.example.eco_rest_retro_mysql.Models.PassengerLoginResponse;
import com.example.eco_rest_retro_mysql.R;

import org.jetbrains.annotations.NotNull;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PassengerLoginActivity extends AppCompatActivity implements View.OnClickListener {

    private EditText editTextUsername, editTextPassword;
    public static String currentUser;
    public static int currentUserId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_passenger_login);

        editTextUsername = findViewById(R.id.editTextUsername);
        editTextPassword = findViewById(R.id.editTextPassword);

        findViewById(R.id.textViewRegister).setOnClickListener(this);
        findViewById(R.id.buttonLogin).setOnClickListener(this);
    }

    private void passengerLogin()
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

        Call<PassengerLoginResponse> call = RetrofitClient.getmInstance().getApi().passengerLogin(username,password);

        call.enqueue(new Callback<PassengerLoginResponse>() {
            @NotNull
            @Override
            public void onResponse(Call<PassengerLoginResponse> call,  Response<PassengerLoginResponse> response) {

                if(!response.body().isError())
                {
                    currentUser = response.body().getPassenger().getUsername();
                    currentUserId = response.body().getPassenger().getPassenger_id();
                    Toast.makeText(PassengerLoginActivity.this, response.body().getMessage() + " Welcome: " + currentUser, Toast.LENGTH_LONG).show();
                    startActivity(new Intent( PassengerLoginActivity.this , PassengerMapsActivity.class ));
                }else
                    {
                        Toast.makeText(PassengerLoginActivity.this, response.body().getMessage(), Toast.LENGTH_LONG).show();
                    }
            }

            @Override
            public void onFailure(Call<PassengerLoginResponse> call, Throwable t) {

            }
        });

    }

    @Override
    public void onClick(View v) {
        switch (v.getId())
        {
            case R.id.buttonLogin:
                passengerLogin();
                break;
            case R.id.textViewRegister:
                startActivity(new Intent(this , PassengerRegisterActivity.class));
                break;
        }

    }

    public static String getCurrentPassengerUsername()
    {
        return currentUser;
    }

    public static int getCurrentPassengerId()
    {
        return currentUserId;
    }

}
