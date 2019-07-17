package com.example.eco_rest_retro_mysql.Models;


public class DefaultLocationResponse {

    private boolean error;
    private Location location;

    public DefaultLocationResponse(boolean error, Location location) {
        this.error = error;
        this.location = location;
    }

    public boolean isError() {
        return error;
    }

    public Location getLocation() {
        return location;
    }
}
