package com.example.eco_rest_retro_mysql.Activity;

import android.content.Intent;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentTransaction;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;

import com.example.eco_rest_retro_mysql.R;
import com.example.eco_rest_retro_mysql.fragments.HistoryFragment;

public class HistoryLoaderPassenger extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_history_loader_passenger);


        Fragment newFragment = new HistoryFragment();
        FragmentTransaction transaction = getSupportFragmentManager().beginTransaction();
        // Replace whatever is in the fragment_container view with this fragment,
        // and add the transaction to the back stack if needed
        transaction.replace(R.id.passengerHistoryLoader, newFragment);
        transaction.addToBackStack(null);
        // Commit the transaction
        transaction.commit();

    }


}
