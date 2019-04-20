package com.example.eco_rest_retro_mysql.Api;


import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.Models.DriverLoginResponse;
import com.example.eco_rest_retro_mysql.Models.LocationResponse;
import com.example.eco_rest_retro_mysql.Models.PassengerLoginResponse;


import retrofit2.Call;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.POST;
import retrofit2.http.PUT;


public interface Api {

        //-------------------------------------------------DRIVER-------------------------------------------------------
        @FormUrlEncoded
        @POST("createdriver")
        Call<DefaultResponse> createDriver
                (
                        @Field("driver_id") String driverId,
                        @Field("driver_name") String driverName,
                        @Field("username") String username,
                        @Field("password") String password,
                        @Field("driver_ic_number") String driverIc,
                        @Field("driver_email") String driverEmail

                );

        @FormUrlEncoded
        @POST("driverlogin")
        Call<DriverLoginResponse> driverLogin
                (
                        @Field("username") String username,
                        @Field("password") String password
                );


        @FormUrlEncoded
        @PUT("drivercurrentposition")
        Call<DefaultResponse> updateDriverCurrentPosition
                (
                        @Field("username") String username,
                        @Field("driver_c_lat") Double driver_c_lat,
                        @Field("driver_c_lng") Double driver_c_lng
                );


        @GET("getdriverlocation")
        Call<LocationResponse> getDriverLocation
                (
                        @Header("username") String username
                );
        //-------------------------------------------------DRIVER--------------------------------------------------------



        //-------------------------------------------------PASSENGER------------------------------------------------------

        @FormUrlEncoded
        @POST("createpassenger")
        Call<DefaultResponse> createPassenger
                (
                        @Field("passenger_id") String passengerId,
                        @Field("passenger_name") String passengerName,
                        @Field("username") String username,
                        @Field("password") String password,
                        @Field("passenger_ic_number") String passengerIc,
                        @Field("passenger_email") String passengerEmail


                );

        @FormUrlEncoded
        @POST("passengerlogin")
        Call<PassengerLoginResponse> passengerLogin
                (
                        @Field("username") String username,
                        @Field("password") String password
                );

        @FormUrlEncoded
        @PUT("passengercurrentposition")
        Call<DefaultResponse> updatePassengerCurrentPosition
                (
                        @Field("username") String username,
                        @Field("passenger_c_lat") Double passenger_c_lat,
                        @Field("passenger_c_lng") Double passenger_c_lng
                );

        //-------------------------------------------------PASSENGER-------------------------------------------------------

}
