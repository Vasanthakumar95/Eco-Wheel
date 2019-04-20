package com.example.eco_rest_retro_mysql.Models;

public class DriverLocation {

    private double driver_c_lat;
    private double driver_c_lng;

    public DriverLocation(double lat, double lng) {
        this.driver_c_lat = lat;
        this.driver_c_lng = lng;
    }

    public double getDriver_c_lat() {
        return driver_c_lat;
    }

    public double getDriver_c_lng() {
        return driver_c_lng;
    }
}
