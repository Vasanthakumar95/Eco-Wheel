package com.example.eco_rest_retro_mysql.Api;


import com.example.eco_rest_retro_mysql.Models.DefaultLocationResponse;
import com.example.eco_rest_retro_mysql.Models.DefaultResponse;
import com.example.eco_rest_retro_mysql.Models.DriverAvailableResponse;
import com.example.eco_rest_retro_mysql.Models.DriverLoginResponse;
import com.example.eco_rest_retro_mysql.Models.HistoryResponse;
import com.example.eco_rest_retro_mysql.Models.PassengerLoginResponse;


import retrofit2.Call;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
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
                        @Field("driver_email") String driverEmail,
                        @Field("driver_car_number") String driverCarNumber,
                        @Field("driver_car_model") String driverCarMode,
                        @Field("contact_number") String contactNumber

                );

        @FormUrlEncoded
        @POST("driverlogin")
        Call<DriverLoginResponse> driverLogin
                (
                        @Field("username") String username,
                        @Field("password") String password
                );

        @FormUrlEncoded
        @POST("scanningassignedtrip")
        Call<DefaultResponse> scanningassignedtrip
                (
                        @Field("driver_id") int driver_id

                );


        @FormUrlEncoded
        @PUT("drivercurrentposition")
        Call<DefaultResponse> updateDriverCurrentPosition
                (
                        @Field("username") String username,
                        @Field("driver_c_lat") Double driver_c_lat,
                        @Field("driver_c_lng") Double driver_c_lng
                );

        @FormUrlEncoded
        @PUT("updatedriverworkingstatus")
        Call<DefaultResponse> updateDriverWorkingStatus
                (
                        @Field("driver_id") int driver_id,
                        @Field("working_status") String working_status

                );

        @FormUrlEncoded
        @POST("getdriverlocation")
        Call<DefaultLocationResponse> getDriverLocation
                (
                        @Field("driver_id") int driver_id

                );


        @GET("getallonlinedriverid")
        Call<DriverAvailableResponse> getAllOnlineDriverId
                (


                );

        @FormUrlEncoded
        @POST("getdriverdestinationlocation")
        Call<DefaultLocationResponse> getDriverDestinationLocation
                (
                        @Field("driver_id") int driver_id

                );

        @FormUrlEncoded
        @POST("getpassengercurrentlocation")
        Call<DefaultLocationResponse> getPassengerCurrentLocation
                (
                        @Field("driver_id") int driver_id

                );

        @FormUrlEncoded
        @POST("current_trip_to_ongoing_trip")
        Call<DefaultResponse> current_trip_to_ongoing_trip
                (
                        @Field("driver_id") int driver_id

                );

        @FormUrlEncoded
        @POST("deletecurrenttrip")
        Call<DefaultResponse> deletecurrenttrip
                (
                        @Field("driver_id") int driver_id

                );

        @FormUrlEncoded
        @PUT("updatepickupstatus")
        Call<DefaultResponse> updatePickUpStatus
                (
                        @Field("driver_id") int driver_id,
                        @Field("picked_up_status") String picked_up_status
                );

        @FormUrlEncoded
        @PUT("updatedropoffstatus")
        Call<DefaultResponse> updatedropoffstatus
                (
                        @Field("driver_id") int driver_id,
                        @Field("picked_up_status") String picked_up_status
                );
        @FormUrlEncoded
        @PUT("updatepaidstatus")
        Call<DefaultResponse> updatepaidstatus
                (
                        @Field("driver_id") int driver_id,
                        @Field("picked_up_status") String picked_up_status
                );

        @FormUrlEncoded
        @POST("ongoing_trip_to_completed_trip")
        Call<DefaultResponse> ongoing_trip_to_completed_trip
                (
                        @Field("driver_id") int driver_id

                );

        @FormUrlEncoded
        @POST("deleteongoingtrip")
        Call<DefaultResponse> deleteongoingtrip
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("getdriverhistory")
        Call<HistoryResponse> getdriverhistory
                (
                        @Field("driver_id") int driver_id

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
                        @Field("passenger_email") String passengerEmail,
                        @Field("contact_number") String contactNumber

                );

        @FormUrlEncoded
        @POST("passengerlogin")
        Call<PassengerLoginResponse> passengerLogin
                (
                        @Field("username") String username,
                        @Field("password") String password
                );

        @FormUrlEncoded
        @POST("passengerlogout")
        Call<DefaultResponse> passengerLogout
                (
                        @Field("username") String username
                );

        @FormUrlEncoded
        @PUT("passengercurrentposition")
        Call<DefaultResponse> updatePassengerCurrentPosition
                (
                        @Field("username") String username,
                        @Field("passenger_c_lat") Double passenger_c_lat,
                        @Field("passenger_c_lng") Double passenger_c_lng
                );

        @FormUrlEncoded
        @POST("passengernewtrip")
        Call<DefaultResponse> passengerNewTrip
                (
                        @Field("trip_id") String trip_id,
                        @Field("passenger_id") int passenger_id,
                        @Field("driver_id") int driver_id,
                        @Field("l_s_lat") Double l_s_lat,
                        @Field("l_s_lng") Double l_s_lng,
                        @Field("l_e_lat") Double l_e_lat,
                        @Field("l_e_lng") Double l_e_lng

                );

        @FormUrlEncoded
        @POST("ispassengerexistbyidcu")
        Call<DefaultResponse> ispassengerexistbyidcu
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("getpassengertripid")
        Call<DefaultResponse> getpassengertripid
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("ispassengerexistbyidon")
        Call<DefaultResponse> ispassengerexistbyidon
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("getpassengerpickedupstatus")
        Call<DefaultResponse> getpassengerpickedupstatus
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("getpassengerdroppedoffstatus")
        Call<DefaultResponse> getpassengerdroppedoffstatus
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("getpassengerpaidstatus")
        Call<DefaultResponse> getpassengerpaidstatus
                (
                        @Field("passenger_id") int passenger_id

                );

        @FormUrlEncoded
        @POST("setpassengerpickedupstatus")
        Call<DefaultResponse> setpassengerpickedupstatus
                (
                        @Field("passenger_id") int passenger_id,
                        @Field("status") String status
                );

        @FormUrlEncoded
        @POST("setpassengerdroppedoffstatus")
        Call<DefaultResponse> setpassengerdroppedoffstatus
                (
                        @Field("passenger_id") int passenger_id,
                        @Field("status") String status
                );

        @FormUrlEncoded
        @POST("setpassengerpaidstatus")
        Call<DefaultResponse> setpassengerpaidstatus
                (
                        @Field("passenger_id") int passenger_id,
                        @Field("status") String status
                );

        @FormUrlEncoded
        @POST("setfarerating")
        Call<DefaultResponse> setfarerating
                (
                        @Field("trip_id") int trip_id,
                        @Field("rate") String rate,
                        @Field("rating") double rating
                );

        @FormUrlEncoded
        @POST("getpassengerhistory")
        Call<HistoryResponse> getpassengerhistory
                (
                        @Field("passenger_id") int passenger_id

                );


        //-------------------------------------------------PASSENGER-------------------------------------------------------

}
