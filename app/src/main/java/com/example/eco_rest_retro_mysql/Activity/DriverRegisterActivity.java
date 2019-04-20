package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Patterns;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.R;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DriverRegisterActivity extends AppCompatActivity implements View.OnClickListener{

    private EditText editTextUsername, editTextPassword, editTextName, editTextIc, editTextEmail;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_register);

        editTextUsername = findViewById(R.id.editTextUsername);
        editTextPassword = findViewById(R.id.editTextPassword);
        editTextName = findViewById(R.id.editTextName);
        editTextIc = findViewById(R.id.editTextIc);
        editTextEmail = findViewById(R.id.editTextEmail);

        findViewById(R.id.textViewLogin).setOnClickListener(this);
        findViewById(R.id.buttonRegister).setOnClickListener(this);

    }



    private void driverSignUp() {
        String id = " ";
        String username = editTextUsername.getText().toString().trim();
        String password = editTextPassword.getText().toString().trim();
        String name = editTextName.getText().toString().trim();
        String ic = editTextIc.getText().toString().trim();
        String email = editTextEmail.getText().toString().trim();

        //error checking from client side input
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

        if(name.isEmpty())
        {
            editTextName.setError("Name is required");
            editTextName.requestFocus();
            return;
        }

        if(ic.length() < 12 || ic.length()>12 || ic.isEmpty())
        {
            editTextIc.setError("Ic No. is required");
            editTextIc.requestFocus();
            return;
        }

        if(email.isEmpty() || !Patterns.EMAIL_ADDRESS.matcher(email).matches())
        {
            editTextPassword.setError("Password is required");
            editTextPassword.requestFocus();
            return;
        }

        Call<DefaultResponse> call = RetrofitClient.getmInstance()
                .getApi()
                .createDriver(id,username,password,name,ic,email);

        call.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

                if(response.code() == 201)
                {
                    DefaultResponse dr =  response.body();

                    Toast.makeText(DriverRegisterActivity.this, dr.getMessage(),Toast.LENGTH_LONG).show();
                }else if(response.code() == 422)
                {
                    Toast.makeText(DriverRegisterActivity.this,"User Already Exist",Toast.LENGTH_LONG).show();
                }

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }



    @Override
    public void onClick(View v) {
        switch(v.getId())
        {
            case R.id.buttonRegister:
                driverSignUp();
                break;

            case R.id.textViewLogin:
                startActivity(new Intent(this,DriverLoginActivity.class));
        }
    }
}
