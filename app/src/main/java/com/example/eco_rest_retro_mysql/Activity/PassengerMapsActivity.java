package com.example.eco_rest_retro_mysql.Activity;

import android.Manifest;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Looper;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentActivity;
import android.os.Bundle;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.Button;
import android.widget.RatingBar;
import android.widget.TextView;
import android.widget.Toast;

import com.directions.route.AbstractRouting;
import com.directions.route.Route;
import com.directions.route.RouteException;
import com.directions.route.Routing;
import com.directions.route.RoutingListener;
import com.example.eco_rest_retro_mysql.Api.RetrofitClient;
import com.example.eco_rest_retro_mysql.Models.DefaultLocationResponse;
import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.Models.DriverAvailableResponse;
import com.example.eco_rest_retro_mysql.R;
import com.example.eco_rest_retro_mysql.fragments.HistoryFragment;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.common.api.Status;
import com.google.android.gms.location.FusedLocationProviderClient;
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
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;
import com.google.android.gms.tasks.OnFailureListener;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.android.libraries.places.api.Places;
import com.google.android.libraries.places.api.model.Place;
import com.google.android.libraries.places.api.net.PlacesClient;
import com.google.android.libraries.places.widget.AutocompleteSupportFragment;
import com.google.android.libraries.places.widget.listener.PlaceSelectionListener;


import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Random;


import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

import static com.google.android.gms.location.LocationServices.getFusedLocationProviderClient;

public class PassengerMapsActivity extends FragmentActivity implements OnMapReadyCallback,
        ActivityCompat.OnRequestPermissionsResultCallback,
        LocationListener, GoogleApiClient.ConnectionCallbacks, GoogleApiClient.OnConnectionFailedListener,
        View.OnClickListener, RoutingListener {

    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1;
    private static  boolean TODO;
    private boolean mPermissionDenied, current_trip = false , ongoing_trip = false , completed_trip = false, requested = false, picked_up = false, dropped_off = false, paid = false;
    private double lat, lng, driverLat, driverLng, ADLat , ADLng;
    private GoogleMap mMap;
    LocationRequest mLocationRequest;
    Location mLastLocation ;
    private GoogleApiClient mGoogleApiClient;
    private static String currentPassenger = PassengerLoginActivity.getCurrentPassengerUsername();
    private static int currentPassengerId = PassengerLoginActivity.getCurrentPassengerId();
    private int driver_size;
    public int[] driver_id_array;
    private int random , tripid;
    final int random_fixed = random;
    private TextView txtView, txtViewDestination, txtViewDistance, txtViewFare, txtViewDriver;
    private LatLng  strt_latlng, des_latlng ;
    private List<Polyline> polylines;
    private static final int[] COLORS = new int[]{R.color.primary_dark_material_light};
    private double distance_m;
    static final int TIME_OUT = 5000;
    static final int MSG_DISMISS_DIALOG = 0;
    private Marker driver;
    private Button request;

    private AlertDialog AlertDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_passenger_maps);
        // Obtain the SupportMapFragment and get notified when the map is ready to be used.
        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);
        getLastLocation();

        findViewById(R.id.logout_button).setOnClickListener(this);
        request = findViewById(R.id.nav_request_ride);
        request.setOnClickListener(this);
        findViewById(R.id.history_button).setOnClickListener(this);

        txtView = findViewById(R.id.txtView);
        txtViewDriver = findViewById(R.id.txtDetailsDriver2);
        txtViewDestination = findViewById(R.id.txtDetailsLocationName2);
        txtViewDistance = findViewById(R.id.txtDetailsDistance2);
        txtViewFare = findViewById(R.id.txtDetailsFare2);

        polylines = new ArrayList<>();

        gettingAllDrivers();
        //places related----------------------------------------------------------------------------------------------
        // Initialize Places.
        Places.initialize(getApplicationContext(), "AIzaSyD8Pczv2uluZWU0nCzZAgPgVCe6gf-tOxw");
        // Create a new Places client instance.
        PlacesClient placesClient = Places.createClient(this);

        // Initialize the AutocompleteSupportFragment.
        AutocompleteSupportFragment autocompleteFragment = (AutocompleteSupportFragment)
                getSupportFragmentManager().findFragmentById(R.id.autocomplete_fragment);

        // Specify the types of place data to return.
        autocompleteFragment.setPlaceFields(Arrays.asList(Place.Field.ID, Place.Field.NAME, Place.Field.LAT_LNG, Place.Field.ADDRESS));

        autocompleteFragment.setOnPlaceSelectedListener(new PlaceSelectionListener() {
            @Override
            public void onPlaceSelected(@NonNull Place place) {
                txtView.setText(place.getName());
                txtViewDestination.setText(place.getAddress());


                des_latlng = place.getLatLng();

                distance_m = distance(lat,lng,des_latlng.latitude,des_latlng.longitude, "K");

                txtViewDistance.setText(String.format("%.02f",distance_m) + " KM");

                txtViewFare.setText(fare(distance_m) );
                Toast.makeText(PassengerMapsActivity.this , fare(distance_m) , Toast.LENGTH_SHORT ).show();

                mMap.addMarker(new MarkerOptions().position(new LatLng(place.getLatLng().latitude , place.getLatLng().longitude)).icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_destination_icon)));

            }

            @Override
            public void onError(@NonNull Status status) {

            }
        });
        //places related end----------------------------------------------------------------------------------------------
    }


    /**
     * Manipulates the map once available.
     * This callback is triggered when the map is ready to be used.
     * This is where we can add markers or lines, add listeners or move the camera. In this case,
     * we just add a marker near Sydney, Australia.
     * If Google Play services is not installed on the device, the user will be prompted to install
     * it inside the SupportMapFragment. This method will only be triggered once the user has
     * installed Google Play services and returned to the app.
     */
    @Override
    public void onMapReady(GoogleMap map) {
        mMap = map;
        buildGoogleApiClient();
        enableMyLocation();
        mMap.getUiSettings().setMyLocationButtonEnabled(false);
        mMap.getUiSettings().setMapToolbarEnabled(false);
        LatLng strtLATLNG = new LatLng(lat,lng);
        mMap.moveCamera(CameraUpdateFactory.newLatLng(strtLATLNG));
        mMap.animateCamera(CameraUpdateFactory.zoomTo(1));


        gettingAllDrivers();


    }




    private void enableMyLocation() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) {
            // Permission to access the location is missing.
            PermissionUtils.requestPermission(
                    PassengerMapsActivity.this, LOCATION_PERMISSION_REQUEST_CODE,
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
                        Log.d("PassengerMapActivity", "Error trying to get last GPS location");
                        e.printStackTrace();
                    }
                });
    }

    @Override
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
        strt_latlng = latLng;
        //http request to input values of location into driver when ever there is a location change
        Call<DefaultResponse> call = RetrofitClient.getmInstance().getApi().updatePassengerCurrentPosition( currentPassenger , lat , lng);

        call.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                DefaultResponse passengerDefaultResponse = response.body();

                if(!passengerDefaultResponse.isError())
                {

                    //Toast.makeText(PassengerMapsActivity.this, passengerDefaultResponse.getMessage() + " lat = " + lat + " lng = " + lng, Toast.LENGTH_SHORT).show();

                }else
                {
                    Toast.makeText(PassengerMapsActivity.this, passengerDefaultResponse.getMessage(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

        mMap.moveCamera(CameraUpdateFactory.newLatLng(latLng));
        mMap.animateCamera(CameraUpdateFactory.zoomTo(15));


        checkingTripInCurrent();
        checkingTripInOngoing();
        checkingPickUpStatus();
        checkingDroppedOffStatus();
        checkingPaidStatus();



        if(!current_trip)
        {
           if(!ongoing_trip)
           {
               if(!requested)
               {
                   //passenger haven't requested a ride
                   drawingAllOnlineDriver();
                   mMap.clear();
               }else
                   {//driver rejected
                       Toast.makeText(this , "Driver Have Rejected Your Request", Toast.LENGTH_SHORT);
                       requested = false;
                       //set requested to false
                       //prompt passenger to request again
                   }
           }else
               {
                   if(!requested)
                   {
                       if(!picked_up)
                       {

                           MarkerOptions options;
                           options = new MarkerOptions();
                           options.position(new LatLng(ADLat ,ADLng));
                           options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_electric_car));
                           driver = mMap.addMarker(options);
                           driver.remove();

                       }else
                           {
                                setPicked_up();
                                erasePolylines();
                                passenger_current_to_destination__route();

                           }


                   }else
                       {
                           mMap.clear();
                           //driver_accept
                           alertDialog();
                           //Toast.makeText(this , "Driver Have Accepted Your Request", Toast.LENGTH_SHORT);
                           //pop up driver details
                           //show location and route of driver to passenger
                           passenger_current_to_driver_current_route();
                           requested=false;

                       }
               }
        }else
            {
                if(!ongoing_trip)
                {
                    if(!requested)
                    {
                    }else
                        {//waiting for driver to accept //
                            //request been made prompt
                        }
                }else
                    {
                        if(!requested)
                        {
                        }else
                            {
                            }
                    }
            }


        if (!dropped_off)
        {
            if(!paid)
            {

            }else
            {

                withRatingBar();
                setPaid();
                delete_ongoing();
            }
        }else
        {
            //please pay prompt
            getCurrentTripId();
            erasePolylines();
            mMap.clear();
            setDropped_Off();
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



    @Override
    public void onConnectionSuspended(int i) {

    }

    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {

    }

    public void passengerLogout()
    {
        Call<DefaultResponse> call = RetrofitClient.getmInstance().getApi().passengerLogout(currentPassenger);

        call.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

                DefaultResponse logoutresponse = response.body();

                if(!logoutresponse.isError())
                {
                    Toast.makeText(PassengerMapsActivity.this, logoutresponse.getMessage(), Toast.LENGTH_SHORT).show();
                }else
                    {
                        Toast.makeText(PassengerMapsActivity.this, "Logout Failed. Restart the App.", Toast.LENGTH_SHORT).show();

                    }

            }
            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });

    }

    public void delete_ongoing()
    {
        Call<DefaultResponse> delete_ongoing = RetrofitClient.getmInstance().getApi().deleteongoingtrip(currentPassengerId);
        delete_ongoing.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });


    }

    private void gettingAllDrivers()
    {
        Call<DriverAvailableResponse> driver = RetrofitClient.getmInstance().getApi().getAllOnlineDriverId();

        driver.enqueue(new Callback<DriverAvailableResponse>() {
            @Override
            public void onResponse(Call<DriverAvailableResponse> call, Response<DriverAvailableResponse> response) {
                ;
                if(!response.body().isError())
                {
                    driver_size = response.body().getDrivers().length;
                    driver_id_array = response.body().getDrivers();
                    random = new Random().nextInt(driver_size);


                    Toast.makeText(PassengerMapsActivity.this, "Available Drivers" , Toast.LENGTH_SHORT).show();
                }else
                {
                    Toast.makeText(PassengerMapsActivity.this, "No Drivers were Found", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<DriverAvailableResponse> call, Throwable t) {

            }
        });

    }

    private void drawingAllOnlineDriver()
    {
        for(int x = 0; x < driver_size ; x++ ) {

            Call<DefaultLocationResponse> drawing_drivers;
            drawing_drivers = RetrofitClient.getmInstance().getApi().getDriverLocation(driver_id_array[x]);


            drawing_drivers.enqueue(new Callback<DefaultLocationResponse>() {
                @Override
                public void onResponse(Call<DefaultLocationResponse> call, Response<DefaultLocationResponse> response) {
                    DefaultLocationResponse defaultLocationResponse = response.body();
                    if (!defaultLocationResponse.isError()) {
                        driverLat = defaultLocationResponse.getLocation().getDriver_lat();
                        driverLng = defaultLocationResponse.getLocation().getDriver_lng();
                        mMap.addMarker(new MarkerOptions().position(new LatLng(driverLat, driverLng)).title("test2" + driver_size).visible(true).icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_electric_car)));
                    }
                }

                @Override
                public void onFailure(Call<DefaultLocationResponse> call, Throwable t) {

                }
            });

        }
    }

    private void passenger_new_trip()
    {
        Call<DefaultResponse> new_trip = RetrofitClient.getmInstance().getApi().passengerNewTrip(" ", currentPassengerId, driver_id_array[random_fixed], strt_latlng.latitude, strt_latlng.longitude, des_latlng.latitude, des_latlng.longitude);

        new_trip.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    Toast.makeText(PassengerMapsActivity.this, response.body().getMessage(), Toast.LENGTH_SHORT).show();
                }else
                {
                    Toast.makeText(PassengerMapsActivity.this, response.body().getMessage(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void checkingTripInCurrent()
    {
        Call<DefaultResponse> check_current = RetrofitClient.getmInstance().getApi().ispassengerexistbyidcu(currentPassengerId);
        check_current.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    current_trip = true;
                }else
                    {
                        current_trip = false;
                    }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void checkingTripInOngoing()
    {
        Call<DefaultResponse> check_ongoing = RetrofitClient.getmInstance().getApi().ispassengerexistbyidon(currentPassengerId);
        check_ongoing.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    ongoing_trip = true;
                }else
                {
                    current_trip = false;
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void checkingPickUpStatus()
    {
        Call<DefaultResponse> check_pickup = RetrofitClient.getmInstance().getApi().getpassengerpickedupstatus(currentPassengerId);
        check_pickup.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                     picked_up = true;
                }else
                {
                    picked_up = false;
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void checkingDroppedOffStatus()
    {
        Call<DefaultResponse> check_pickup = RetrofitClient.getmInstance().getApi().getpassengerdroppedoffstatus(currentPassengerId);
        check_pickup.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    dropped_off = true;
                }else
                {
                    dropped_off = false;
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void checkingPaidStatus()
    {
        Call<DefaultResponse> check_pickup = RetrofitClient.getmInstance().getApi().getpassengerpaidstatus(currentPassengerId);
        check_pickup.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    paid = true;
                }else
                {
                    paid = false;
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void setPicked_up()
    {
        Call<DefaultResponse> set_pick_up = RetrofitClient.getmInstance().getApi().setpassengerpickedupstatus(currentPassengerId,"false");
        set_pick_up.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
            }
            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {
            }
        });
    }

    private void setDropped_Off()
    {
        Call<DefaultResponse> set_drop_off = RetrofitClient.getmInstance().getApi().setpassengerdroppedoffstatus(currentPassengerId,"false");
        set_drop_off.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
            }
            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {
            }
        });
    }

    private void setPaid()
    {
        Call<DefaultResponse> set_paid = RetrofitClient.getmInstance().getApi().setpassengerpaidstatus(currentPassengerId,"false");
        set_paid.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
            }
            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {
            }
        });
    }



    public void alertDialog()
    {
        AlertDialog.Builder dialog=new AlertDialog.Builder(this);
        dialog.setIcon(R.mipmap.ic_launcher_man_icon);
        dialog.setMessage("");
        dialog.setMessage("Driver Id: " + driver_id_array[random_fixed]);
        dialog.setTitle("Driver Found");
        dialog.setNeutralButton("OK",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog,
                                        int which)
                    {

                    }
                });
        AlertDialog alertDialog=dialog.create();
        alertDialog.show();
    }

    private void assignedDriverLatLng()
    {
        Call<DefaultLocationResponse> assigned_Driver;
        assigned_Driver = RetrofitClient.getmInstance().getApi().getDriverLocation(driver_id_array[random_fixed]);
        assigned_Driver.enqueue(new Callback<DefaultLocationResponse>() {
            @Override
            public void onResponse(Call<DefaultLocationResponse> call, Response<DefaultLocationResponse> response) {
                ADLat = response.body().getLocation().getDriver_lat();
                ADLng = response.body().getLocation().getDriver_lng();
            }
            @Override
            public void onFailure(Call<DefaultLocationResponse> call, Throwable t) {
            }
        });
    }

    private void  drawingassignedDriver()
    {
        MarkerOptions options = new MarkerOptions();
        // End marker
        options = new MarkerOptions();
        options.position(new LatLng(ADLat, ADLng));
        options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_electric_car));
        mMap.addMarker(options);
    }

    public void getCurrentTripId()
    {
        final Call<DefaultResponse> trip_id = RetrofitClient.getmInstance().getApi().getpassengertripid(currentPassengerId);
        trip_id.enqueue(new Callback<DefaultResponse>() {
            @Override
            public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {
                if(!response.body().isError())
                {
                    tripid = Integer.parseInt(response.body().getMessage());
                }
            }

            @Override
            public void onFailure(Call<DefaultResponse> call, Throwable t) {

            }
        });
    }

    private void passenger_current_to_driver_current_route()
    {
        Routing routing = new Routing.Builder()
                .travelMode(AbstractRouting.TravelMode.DRIVING)
                .withListener(this)
                .waypoints(new LatLng(lat,lng), new LatLng(ADLat, ADLng))
                .key("AIzaSyD8Pczv2uluZWU0nCzZAgPgVCe6gf-tOxw")
                .build();
        routing.execute();
        MarkerOptions options ;

        // End marker
        /*options = new MarkerOptions();
        options.position(new LatLng(ADLat, ADLng));
        options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_electric_car));

        mMap.addMarker(options);*/
    }

    private void passenger_current_to_destination__route() {

        Routing routing = new Routing.Builder()
                .travelMode(AbstractRouting.TravelMode.DRIVING)
                .withListener(this)
                .waypoints(new LatLng(lat,lng), new LatLng(des_latlng.latitude , des_latlng.longitude))
                .key("AIzaSyD8Pczv2uluZWU0nCzZAgPgVCe6gf-tOxw")
                .build();
        routing.execute();
        MarkerOptions options ;

        // End marker
        options = new MarkerOptions();
        options.position(new LatLng(des_latlng.latitude , des_latlng.longitude));
        options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_launcher_destination_icon));
        mMap.addMarker(options);
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
            polyOptions.width(7 + i * 3);
            polyOptions.addAll(route.get(i).getPoints());
            Polyline polyline = mMap.addPolyline(polyOptions);
            polylines.add(polyline);
            Toast.makeText(getApplicationContext(),"Route "+ (i+1) +": distance - "+ route.get(i).getDistanceValue()+": duration - "+ route.get(i).getDurationValue(),Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public void onRoutingCancelled() {

    }


    public void erasePolylines()
    {
        for(Polyline line: polylines)
        {
            line.remove();
        }
        polylines.clear();
    }

    private double distance(double lat1, double lon1, double lat2, double lon2, String unit) {
        if ((lat1 == lat2) && (lon1 == lon2)) {
            return 0;
        }
        else {
            double theta = lon1 - lon2;
            double dist = Math.sin(Math.toRadians(lat1)) * Math.sin(Math.toRadians(lat2)) + Math.cos(Math.toRadians(lat1)) * Math.cos(Math.toRadians(lat2)) * Math.cos(Math.toRadians(theta));
            dist = Math.acos(dist);
            dist = Math.toDegrees(dist);
            dist = dist * 60 * 1.1515;
            if (unit == "K") {
                dist = dist * 1.609344;
            } else if (unit == "N") {
                dist = dist * 0.8684;
            }
            return (dist);
        }
    }

    private String fare(double distance)
    {
        double base = 1.00;
        double per_k = 0.60;

        double total_pay = (base + (distance * per_k));

        String payout  = String.format("%.02f",total_pay) ;

        return payout;
    }

    public void withRatingBar() {

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        LayoutInflater inflater = getLayoutInflater();
        builder.setTitle("Trip Rating");
        View dialogLayout = inflater.inflate(R.layout.alert_dialog_with_rating_bar, null);
        final RatingBar ratingBar = dialogLayout.findViewById(R.id.ratingBar);
        builder.setView(dialogLayout);
        builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {

            update_fare_rating(ratingBar.getRating());

            }
        });
        builder.show();
    }


    public void update_fare_rating(double rate)
    {
            Call<DefaultResponse> fare_rating = RetrofitClient.getmInstance().getApi().setfarerating(tripid, fare(distance_m), rate);
            fare_rating.enqueue(new Callback<DefaultResponse>() {
                @Override
                public void onResponse(Call<DefaultResponse> call, Response<DefaultResponse> response) {

                }

                @Override
                public void onFailure(Call<DefaultResponse> call, Throwable t) {

                }
            });
    }

    @Override
    public void onClick(View v) {

        switch (v.getId())
        {
            case R.id.logout_button:
                passengerLogout();
                startActivity(new Intent( this , PassengerLoginActivity.class));
                break;

            case R.id.nav_request_ride:
                passenger_new_trip();
                assignedDriverLatLng();
                requested = true;
                break;

            case R.id.history_button:
                startActivity(new Intent( this , HistoryLoaderPassenger.class));
                break;
        }
    }

}
