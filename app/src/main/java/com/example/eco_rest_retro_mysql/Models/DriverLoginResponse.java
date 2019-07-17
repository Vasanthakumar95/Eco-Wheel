package com.example.eco_rest_retro_mysql.Models;

public class DriverLoginResponse {

    private boolean error = true;
    private String message;
    private Driver driver;

    public DriverLoginResponse(boolean error, String message, Driver driver) {
        this.error = error;
        this.message = message;
        this.driver = driver;
    }

    public boolean isError() { return error; }

    public String getMessage() {
        return message;
    }

    public Driver getDriver() {
        return driver;
    }
}
