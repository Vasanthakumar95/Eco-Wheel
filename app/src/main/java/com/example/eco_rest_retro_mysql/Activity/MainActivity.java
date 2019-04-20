package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Patterns;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import com.example.eco_rest_retro_mysql.R;


public class MainActivity extends AppCompatActivity implements View.OnClickListener{




    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);


        findViewById(R.id.buttonDriver).setOnClickListener(this);
        findViewById(R.id.buttonPassenger).setOnClickListener(this);

    }

    @Override
    public void onClick(View v) {
        switch(v.getId())
        {
            case R.id.buttonDriver:
                startActivity(new Intent(this, DriverLoginActivity.class));
                break;

            case R.id.buttonPassenger:
                startActivity(new Intent(this, PassengerLoginActivity.class));
                break;
        }
    }


}

