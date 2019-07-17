package com.example.eco_rest_retro_mysql.Models;

import java.util.ArrayList;
import java.util.List;

public class HistoryResponse {

    private boolean error;

    private List<History> history;


    public HistoryResponse(boolean error, List<History> history) {
        this.error = error;
        this.history = history;
    }

    public boolean isError() {
        return error;
    }

    public List<History> getHistory() {
        return history;
    }
}
