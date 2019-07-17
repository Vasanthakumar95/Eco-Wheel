package com.example.eco_rest_retro_mysql.Models;



public class Location {

    private double driver_lat;
    private double driver_lng;

    public Location(double driver_lat, double driver_lng) {
        this.driver_lat = driver_lat;
        this.driver_lng = driver_lng;
    }

    public double getDriver_lat() {
        return driver_lat;
    }

    public double getDriver_lng() {
        return driver_lng;
    }
}
