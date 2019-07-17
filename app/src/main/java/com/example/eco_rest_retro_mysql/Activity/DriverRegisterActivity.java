package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Patterns;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.R;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DriverRegisterActivity extends AppCompatActivity implements View.OnClickListener , AdapterView.OnItemSelectedListener {

    private EditText editTextUsername, editTextPassword, editTextName, editTextIc, editTextEmail, editTextCarNumber, editTextContactNumber;
    private String carModel;
    private Button register;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_register);

        editTextUsername = findViewById(R.id.editTextUsername);
        editTextPassword = findViewById(R.id.editTextPassword);
        editTextName = findViewById(R.id.editTextName);
        editTextIc = findViewById(R.id.editTextIc);
        editTextEmail = findViewById(R.id.editTextEmail);
        editTextCarNumber = findViewById(R.id.editTextCarNumber);
        editTextContactNumber = findViewById(R.id.editTextContactNumber);

        findViewById(R.id.textViewLogin).setOnClickListener(this);
        register = findViewById(R.id.buttonRegister);
        register.setOnClickListener(this);

        Spinner spinner = findViewById(R.id.cars_spinner);
        spinner.setOnItemSelectedListener(this);
        spinner.setSelection(0);


// Create an ArrayAdapter using the string array and a default spinner layout
        ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(this,
                R.array.cars_array, android.R.layout.simple_spinner_item);
// Specify the layout to use when the list of choices appears
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
// Apply the adapter to the spinner
        spinner.setAdapter(adapter);

    }



    private void driverSignUp() {
        String id = " ";
        String username = editTextUsername.getText().toString().trim();
        String password = editTextPassword.getText().toString().trim();
        String name = editTextName.getText().toString().trim();
        String ic = editTextIc.getText().toString().trim();
        String email = editTextEmail.getText().toString().trim();
        String car_num = editTextCarNumber.getText().toString().trim();
        String contact_num = editTextContactNumber.getText().toString().trim();

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

        if(car_num.isEmpty())
        {
            editTextPassword.setError("Car No. is required");
            editTextPassword.requestFocus();
            return;
        }

        if(contact_num.isEmpty())
        {
            editTextPassword.setError("Contact No. is required");
            editTextPassword.requestFocus();
            return;
        }

        Call<DefaultResponse> call = RetrofitClient.getmInstance()
                .getApi()
                .createDriver(id,username,password,name,ic,email, car_num, carModel, contact_num);

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

    @Override
    public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

        carModel = parent.getItemAtPosition(position).toString();//to get values from a selected item

        if(carModel == parent.getItemAtPosition(0).toString())
        {
            register.setEnabled(false);
        }else
        {
            register.setEnabled(true);
        }

    }

    @Override
    public void onNothingSelected(AdapterView<?> parent) {
            register.setEnabled(false);
    }
}
