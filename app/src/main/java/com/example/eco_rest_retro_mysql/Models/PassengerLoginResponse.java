package com.example.eco_rest_retro_mysql.Models;

public class PassengerLoginResponse {

    private boolean error;
    private String message;
    private Passenger passenger;

    public PassengerLoginResponse(boolean error, String message, Passenger passenger) {
        this.error = error;
        this.message = message;
        this.passenger = passenger;
    }

    public boolean isError() {
        return error;
    }

    public String getMessage() {
        return message;
    }

    public Passenger getPassenger() { return passenger; }
}
