package com.example.eco_rest_retro_mysql.Models;

public class Passenger {

    private int id;
    private String username;

    public Passenger(int id, String username) {
        this.id = id;
        this.username = username;
    }

    public int getId() {
        return id;
    }

    public String getUsername() {
        return username;
    }
}
