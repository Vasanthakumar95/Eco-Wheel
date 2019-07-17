package com.example.eco_rest_retro_mysql.Adapter;

import android.content.Context;
import android.support.annotation.NonNull;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.example.eco_rest_retro_mysql.Models.History;
import com.example.eco_rest_retro_mysql.R;

import java.util.ArrayList;
import java.util.List;

public class HistoryAdapter extends RecyclerView.Adapter<HistoryAdapter.HistoryViewHolder> {

    private Context mCtx;
    private List<History> historyList;

    public HistoryAdapter(Context mCtx, List<History> historyList) {
        this.mCtx = mCtx;
        this.historyList = historyList;
    }

    @NonNull
    @Override
    public HistoryViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewTypr) {
        View view = LayoutInflater.from(mCtx).inflate(R.layout.recyclerview_history , parent , false);
        return new HistoryViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull HistoryViewHolder historyViewHolder, int position) {
        History history = historyList.get(position);

        historyViewHolder.trip_id.setText(Integer.toString(history.getTrip_id()));
        historyViewHolder.driver_id.setText(Integer.toString(history.getDriver_id()));
        historyViewHolder.passenger_id.setText(Integer.toString(history.getPassenger_id()));
        historyViewHolder.rating.setText(Double.toString(history.getRating()));
    }

    @Override
    public int getItemCount() {
        return historyList.size();
    }

    class HistoryViewHolder extends RecyclerView.ViewHolder
    {

        TextView trip_id, passenger_id, driver_id, rating;

        public HistoryViewHolder(@NonNull View itemView) {
            super(itemView);

            trip_id = itemView.findViewById(R.id.trip_id);
            passenger_id = itemView.findViewById(R.id.passenger_id);
            driver_id = itemView.findViewById(R.id.driver_id);
            rating = itemView.findViewById(R.id.rating);
        }
    }
}
