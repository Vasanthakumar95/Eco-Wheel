package com.example.eco_rest_retro_mysql.Models;

public class LocationResponse {

    private boolean error;
    private DriverLocation location;

    public LocationResponse(boolean er ,DriverLocation dl) {
        this.error = er;
        this.location = dl;
    }

    public boolean isError() {
        return error;
    }

    public DriverLocation location() {
        return location;
    }
}
