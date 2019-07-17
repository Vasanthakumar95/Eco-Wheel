package com.example.eco_rest_retro_mysql.Models;

public class Driver {

    private int driver_id;
    private String username;

    public Driver(int id, String username) {
        this.driver_id = id;
        this.username = username;
    }

    public int getDriver_id() {
        return driver_id;
    }

    public String getUsername() {
        return username;
    }
}
