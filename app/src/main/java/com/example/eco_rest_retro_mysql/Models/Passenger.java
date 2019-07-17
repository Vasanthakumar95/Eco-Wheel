package com.example.eco_rest_retro_mysql.Models;

public class Passenger {

    private int passenger_id;
    private String username;

    public Passenger(int id, String username) {
        this.passenger_id = id;
        this.username = username;
    }

    public int getPassenger_id() {
        return passenger_id;
    }

    public String getUsername() {
        return username;
    }
}
