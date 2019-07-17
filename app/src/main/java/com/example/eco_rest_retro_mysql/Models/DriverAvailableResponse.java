package com.example.eco_rest_retro_mysql.Models;


public class DriverAvailableResponse {

    private boolean error;

    private int[] drivers;

    public DriverAvailableResponse(boolean error, int[] drivers) {
        this.error = error;
        this.drivers = drivers;
    }

    public boolean isError() {
        return error;
    }

    public int[] getDrivers() {
        return drivers;
    }
}
