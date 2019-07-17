package com.example.eco_rest_retro_mysql.Activity;

import android.Manifest;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.FragmentActivity;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.CompoundButton;
import android.widget.ProgressBar;
import android.widget.Switch;
import android.widget.Toast;


import com.directions.route.AbstractRouting;
import com.directions.route.Route;
import com.directions.route.RouteException;
import com.directions.route.Routing;
import com.directions.route.RoutingListener;
import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DefaultLocationResponse;
import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.R;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationListener;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.LocationSettingsRequest;
import com.google.android.gms.location.SettingsClient;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;
import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;


import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

import static com.google.android.gms.location.LocationServices.getFusedLocationProviderClient;


public class DriverMapsActivity extends FragmentActivity implements OnMapReadyCallback,
        ActivityCompat.OnRequestPermissionsResultCallback,
        LocationListener, GoogleApiClient.ConnectionCallbacks, GoogleApiClient.OnConnectionFailedListener,
        View.OnClickListener, RoutingListener {


    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1;
    private static  boolean TODO;
    private boolean mPermissionDenied;
    private double lat, lng, passenger_current_lat, passenger_current_lng, destination_lat, destination_lng;
    private GoogleMap mMap;
    LocationRequest mLocationRequest;
    Location mLastLocation;
    private GoogleApiClient mGoogleApiClient;
    private static String currentDriver = DriverLoginActivity.getCurrentDriverUsername();
    private static int currentDriverId = DriverLoginActivity.getCurrentDriverId();
    public boolean trip_found = false;
    private Switch working_switch;
    private Button accept_trip , dropped_off, paid;
    private List<Polyline> polylines;
    private static final int[] COLORS = new int[]{R.color.primary_dark_material_light};






    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_maps);

        working_switch = findViewById(R.id.nav_working_switch);
        accept_trip = findViewById(R.id.nav_accept_trip);
        accept_trip.setOnClickListener(this);

        dropped_off = findViewById(R.id.nav_dropped_off);
        dropped_off.setOnClickListener(this);

        paid = findViewById(R.id.nav_paid);
        paid.setOnClickListener(this);

        polylines = new ArrayList<>();



        SupportMapFragment mapFragment =
                (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);
        getLastLocation();


    }

    public void onMapReady(GoogleMap map) {

        mMap = map;
        buildGoogleApiClient();
        enableMyLocation();
        mMap.getUiSettings().setMyLocationButtonEnabled(false);
        LatLng strtLATLNG = new LatLng(lat,lng);


    }

    private void enableMyLocation() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) {
            // Permission to access the location is missing.
            PermissionUtils.requestPermission(
                    DriverMapsActivity.this, LOCATION_PERMISSION_REQUEST_CODE,
                    Manifest.permission.ACCESS_FINE_LOCATION, true);
        } else if (mMap != null) {
            // Access to the location has been granted to the app.
            mMap.setMyLocationEnabled(true);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions,
                                           @NonNull int[] grantResults) {
        if (requestCode != LOCATION_PERMISSION_REQUEST_CODE) {
            return;
        }

        if (PermissionUtils.isPermissionGranted(permissions, grantResults,
                Manifest.permission.ACCESS_FINE_LOCATION)) {
            // Enable the my location layer if the permission has been granted.
            enableMyLocation();
        } else {
            // Display the missing permission error dialog when the fragments resume.
            mPermissionDenied = true;
        }
    }

    @Override
    protected void onResumeFragments() {
        super.onResumeFragments();
        if (mPermissionDenied) {
            // Permission was not granted, display error dialog.
            showMissingPermissionError();
            mPermissionDenied = false;
        }
    }

    /**
     * Displays a dialog with error message explaining that the location permission is missing.
     */
    private void showMissingPermissionError() {
        PermissionUtils.PermissionDeniedDialog
                .newInstance(true).show(getSupportFragmentManager(), "dialog");
    }


    public void getLastLocation() {
        // Get last known recent location using new Google Play Services SDK (v11+)
        FusedLocationProviderClient locationClient = getFusedLocationProviderClient(this);

        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            return;
        }
        locationClient.getLastLocation()
                .addOnSuccessListener(new OnSuccessListener<Location>() {
                    @Override
                    public void onSuccess(Location location) {
                        // GPS location can be null if GPS is switched off
                        if (mLastLocation != null) {
                            onLocationChanged(mLastLocation);

                        }
                    }
                })
                .addOnFailureListener(new OnFailureListener() {
                    @Override
                    public void onFailure(@NonNull Exception e) {
                        Log.d("DriverMapActivity", "Error trying to get last GPS location");
                        e.printStackTrace();
                    }
                });
    }

    public void onConnected(@Nullable Bundle bundle) {
        // Create the location request to start receiving updates
        mLocationRequest = new LocationRequest();
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        mLocationRequest.setInterval(1000);
        mLocationRequest.setFastestInterval(1000);

        // Create LocationSettingsRequest object using location request
        LocationSettingsRequest.Builder builder = new LocationSettingsRequest.Builder();
        builder.addLocationRequest(mLocationRequest);
        LocationSettingsRequest locationSettingsRequest = builder.build();

        // Check whether location settings are satisfied
        // https://developers.google.com/android/reference/com/google/android/gms/location/SettingsClient
        SettingsClient settingsClient = LocationServices.getSettingsClient(this);
        settingsClient.checkLocationSettings(locationSettingsRequest);

        // new Google API SDK v11 uses getFusedLocationProviderClient(this)
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {

            return;
        }
        getFusedLocationProviderClient(this).requestLocationUpdates(mLocationRequest, new LocationCallback() {
                    @Override
                    public void onLocationResult(LocationResult locationResult) {
                        // do work here
                        onLocationChanged(locationResult.getLastLocation());
                    }
                },
                Looper.myLooper());



    }

    @Override
    public void onLocationChanged(Location location) {
        mLastLocation = location;

        lat = location.getLatitude();
        lng = location.getLongitude();

        LatLng latLng = new LatLng(location.getLatitude(),location.getLongitude() );

        //http request to input values of location into driver when ever there is a location change
        Call<DefaultResponse> call = RetrofitClient.getmInstance().getApi().updateDriverCurrentPosition( currentDriver , lat , lng);

        call.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                DefaultResponse driverDefaultResponse = response.body();

                if(!driverDefaultResponse.isError())
                {

                   // Toast.makeText(DriverMapsActivity.this, driverDefaultResponse.getMessage() + " lat = " + lat + " lng = " + lng, Toast.LENGTH_SHORT).show();

                }else
                {
                    Toast.makeText(DriverMapsActivity.this, driverDefaultResponse.getMessage(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

        mMap.moveCamera(CameraUpdateFactory.newLatLng(latLng));
        mMap.animateCamera(CameraUpdateFactory.zoomTo(15));

        changeWorkingStatus();
        getDestination();
        getPassengerCurrentLocation();

        if(!trip_found)
        {
            if (!working_switch.isChecked())
            {

            }else
                {
                    scanningAvailableTrip();
                }

        }else
            {

                if (!working_switch.isChecked())
                {

                }else
                    {
                        alertDialog();
                        trip_found = false;
                        working_switch.setChecked(false);
                    }

            }
    }

    private synchronized void buildGoogleApiClient() {
        mGoogleApiClient = new GoogleApiClient.Builder(this)
                .addConnectionCallbacks(this)
                .addOnConnectionFailedListener(this)
                .addApi(LocationServices.API)
                .build();
        mGoogleApiClient.connect();
    }

    public void getDestination()
    {
        Call<DefaultLocationResponse> destination = RetrofitClient.getmInstance().getApi().getDriverDestinationLocation(currentDriverId);
        destination.enqueue(new Callback<DefaultLocationResponse>() {
            @Override
            public void onResponse(Call<DefaultLocationResponse> call, Response<DefaultLocationResponse> response) {
                if(!response.body().isError()) {
                    destination_lat = response.body().getLocation().getDriver_lat();
                    destination_lng = response.body().getLocation().getDriver_lng();
                    //Toast.makeText(DriverMapsActivity.this, "Destination: " + destination_lat +","+ destination_lng, Toast.LENGTH_LONG).show();

                }else
                    {
                        //Toast.makeText(DriverMapsActivity.this, "Destination: " + destination_lat + destination_lng, Toast.LENGTH_LONG).show();

                    }
            }

            @Override
            public void onFailure(Call<DefaultLocationResponse> call, Throwable t) {

            }
        });
    }

    public void getPassengerCurrentLocation()
    {
        Call<DefaultLocationResponse> passengerCurrent = RetrofitClient.getmInstance().getApi().getPassengerCurrentLocation(currentDriverId);
        passengerCurrent.enqueue(new Callback<DefaultLocationResponse>() {
            @Override
            public void onResponse(Call<DefaultLocationResponse> call, Response<DefaultLocationResponse> response) {
                if (!response.body().isError())
                {
                    passenger_current_lat = response.body().getLocation().getDriver_lat();
                    passenger_current_lng = response.body().getLocation().getDriver_lng();
                }
            }

            @Override
            public void onFailure(Call<DefaultLocationResponse> call, Throwable t) {

            }
        });

    }

    //querying database for any available trips that was assigned
    public void scanningAvailableTrip()
    {

        Call<DefaultResponse> find_trip = RetrofitClient.getmInstance().getApi().scanningassignedtrip(currentDriverId);

        find_trip.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    trip_found = true;
                }
                else
                    {
                        trip_found = false;
                    }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });


    }




    public void migrate_current_ongoing_delete()
    {
        Call<DefaultResponse> current_to_ongoing = RetrofitClient.getmInstance().getApi().current_trip_to_ongoing_trip(currentDriverId);
        current_to_ongoing.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

    }

    public void delete_current()
    {

      Call<DefaultResponse> delete_current = RetrofitClient.getmInstance().getApi().deletecurrenttrip(currentDriverId);
        delete_current.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

    }

    public void updatePickUpStatus()
    {

        Call<DefaultResponse> pickUpStatus = RetrofitClient.getmInstance().getApi().updatePickUpStatus(currentDriverId , "true");
        pickUpStatus.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });


    }

    public void updateDroppedOffStatus()
    {

        Call<DefaultResponse> droppedOffStatus = RetrofitClient.getmInstance().getApi().updatedropoffstatus(currentDriverId , "true");
        droppedOffStatus.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    public void updatePaidStatus()
    {

        Call<DefaultResponse> paidStatus = RetrofitClient.getmInstance().getApi().updatepaidstatus(currentDriverId , "true");
        paidStatus.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }


    public void migrate_ongoing_complete_delete()
    {
        Call<DefaultResponse> ongoing = RetrofitClient.getmInstance().getApi().ongoing_trip_to_completed_trip(currentDriverId);
        ongoing.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                    if(!response.body().isError())
                    {

                    }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

    }




    private void driver_current_to_passenger_current_route()
    {
        Routing routing = new Routing.Builder()
                .travelMode(AbstractRouting.TravelMode.DRIVING)
                .withListener(this)
                .waypoints(new LatLng(lat,lng), new LatLng(passenger_current_lat, passenger_current_lng))
                .key("AIzaSyD8Pczv2uluZWU0nCzZAgPgVCe6gf-tOxw")
                .build();
        routing.execute();


        MarkerOptions options = new MarkerOptions();


        // End marker
        options = new MarkerOptions();
        options.position(new LatLng(passenger_current_lat,passenger_current_lng));
        options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_man_icon));

        mMap.addMarker(options);
    }

    private void driver_current_to_destination_route() {

        Routing routing = new Routing.Builder()
                .travelMode(AbstractRouting.TravelMode.DRIVING)
                .withListener(this)
                .waypoints(new LatLng(lat,lng), new LatLng(destination_lat,destination_lng))
                .key("AIzaSyD8Pczv2uluZWU0nCzZAgPgVCe6gf-tOxw")
                .build();
        routing.execute();

        MarkerOptions options = new MarkerOptions();


        // End marker
        options = new MarkerOptions();
        options.position(new LatLng(destination_lat,destination_lng));
        options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_destination_icon));
        mMap.addMarker(options);
    }


    public void changeWorkingStatus()
    {

        working_switch.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if(isChecked)
                {
                    Call<DefaultResponse> working = RetrofitClient.getmInstance().getApi().updateDriverWorkingStatus(currentDriverId , "true");
                    working.enqueue(new Callback<DefaultResponse>() {
                        @Override
                        public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                            Toast.makeText(DriverMapsActivity.this, response.body().getMessage(), Toast.LENGTH_SHORT).show();

                        }

                        @Override
                        public void onFailure(Call<DefaultResponse> call, Throwable t) {

                        }
                    });
                }else
                    {
                        Call<DefaultResponse> not_working = RetrofitClient.getmInstance().getApi().updateDriverWorkingStatus(currentDriverId , "false");
                        not_working.enqueue(new Callback<DefaultResponse>() {
                            @Override
                            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                                Toast.makeText(DriverMapsActivity.this, response.body().getMessage(), Toast.LENGTH_SHORT).show();

                            }

                            @Override
                            public void onFailure(Call<DefaultResponse> call, Throwable t) {

                            }
                        });
                    }
            }
        });

    }

    @Override
    public void onConnectionSuspended(int i) {

    }

    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {

    }


    public void ButtonPickedPassenger()
    {
        accept_trip.setVisibility(View.VISIBLE);


    }

    public void ButtonDroppedPassenger()
    {
        accept_trip.setVisibility(View.INVISIBLE);
        accept_trip.setEnabled(false);
        dropped_off.setVisibility(View.VISIBLE);

    }

    public void ButtonPaidPassenger()
    {
        dropped_off.setVisibility(View.INVISIBLE);
        dropped_off.setEnabled(false);
        paid.setVisibility(View.VISIBLE);

    }

    public void ButtonafterPaidPassenger()
    {

        paid.setVisibility(View.INVISIBLE);
        accept_trip.setEnabled(true);
        dropped_off.setEnabled(true);

    }

    @Override
    public void onRoutingFailure(RouteException e) {

        if(e != null) {
            Toast.makeText(this, "Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
        }else {
            Toast.makeText(this, "Something went wrong, Try again", Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public void onRoutingStart() {

    }

    @Override
    public void onRoutingSuccess(ArrayList<Route> route, int shortestRouteIndex) {

        if(polylines.size()>0) {
            for (Polyline poly : polylines) {
                poly.remove();
            }
        }

        polylines = new ArrayList<>();
        //add route(s) to the map.
        for (int i = 0; i <route.size(); i++) {

            //In case of more than 5 alternative routes
            int colorIndex = i % COLORS.length;

            PolylineOptions polyOptions = new PolylineOptions();
            polyOptions.color(getResources().getColor(COLORS[colorIndex]));
            polyOptions.width(10 + i * 3);
            polyOptions.addAll(route.get(i).getPoints());
            Polyline polyline = mMap.addPolyline(polyOptions);
            polylines.add(polyline);

            Toast.makeText(getApplicationContext(),"Route "+ (i+1) +": distance - "+ route.get(i).getDistanceValue()+": duration - "+ route.get(i).getDurationValue(),Toast.LENGTH_SHORT).show();
        }

    }

    @Override
    public void onRoutingCancelled() {

    }

    //pop up message after trip is found assigned to current driver
    public void alertDialog()
    {
        AlertDialog.Builder dialog=new AlertDialog.Builder(this);
        dialog.setIcon(R.mipmap.ic_launcher_man_icon);
        dialog.setMessage("Please Accept the Trip");
        dialog.setTitle("Trip Found");
        dialog.setPositiveButton("Accept",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog,
                                        int which) {
                        getDestination();
                        ButtonPickedPassenger();
                        driver_current_to_passenger_current_route();
                        migrate_current_ongoing_delete();
                        delete_current();
                    }
                });
        dialog.setNegativeButton("Decline",new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                //delete from current so passenger can request again
                delete_current();
            }
        });
        AlertDialog alertDialog=dialog.create();
        alertDialog.show();
    }

    public void erasePolylines()
    {
        for(Polyline line: polylines)
        {
            line.remove();
        }
        polylines.clear();
    }

    @Override
    public void onClick(View v) {
        switch (v.getId())
        {
            //after accept trip from pop-up msg
            case R.id.nav_accept_trip:
                    erasePolylines();
                    driver_current_to_destination_route();
                    updatePickUpStatus();
                    ButtonDroppedPassenger();
                break;

            case R.id.nav_dropped_off:
                updateDroppedOffStatus();
                ButtonPaidPassenger();
                erasePolylines();
                break;

            case R.id.nav_paid:
                updatePaidStatus();
                mMap.clear();
                migrate_ongoing_complete_delete();
                ButtonafterPaidPassenger();
                break;
        }
    }




}


