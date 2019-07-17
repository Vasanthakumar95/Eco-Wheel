package com.example.eco_rest_retro_mysql.Models;

public class History {

    private int driver_id , passenger_id, trip_id;
    private double location_start_lat, location_start_lng, location_end_lat, location_end_lng, rating;


    public History(int driver_id, int passenger_id, int trip_id, double location_start_lat, double location_start_lng, double location_end_lat, double location_end_lng, double rating) {
        this.driver_id = driver_id;
        this.passenger_id = passenger_id;
        this.trip_id = trip_id;
        this.location_start_lat = location_start_lat;
        this.location_start_lng = location_start_lng;
        this.location_end_lat = location_end_lat;
        this.location_end_lng = location_end_lng;
        this.rating = rating;
    }

    public int getDriver_id() {
        return driver_id;
    }

    public int getPassenger_id() {
        return passenger_id;
    }

    public int getTrip_id() {
        return trip_id;
    }

    public double getLocation_start_lat() {
        return location_start_lat;
    }

    public double getLocation_start_lng() {
        return location_start_lng;
    }

    public double getLocation_end_lat() {
        return location_end_lat;
    }

    public double getLocation_end_lng() {
        return location_end_lng;
    }

    public double getRating() {
        return rating;
    }
}
