<?php

namespace App\Http\Controllers;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ReservationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ], [
            'vehicle_id.required' => 'The vehicle ID is required.',
            'vehicle_id.exists' => 'The selected vehicle ID is invalid.',
            'start_time.required' => 'The start time is required.',
            'start_time.date' => 'The start time must be a valid date.',
            'start_time.after_or_equal' => 'The start time must be a date after or equal to now.',
            'end_time.required' => 'The end time is required.',
            'end_time.date' => 'The end time must be a valid date.',
            'end_time.after' => 'The end time must be a date after the start time.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        $overlap = Reservation::where('vehicle_id', $request->vehicle_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($query) use ($request) {
                          $query->where('start_time', '<=', $request->start_time)
                                ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['error' => 'The vehicle is already reserved for the selected time period.'], 409);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'vehicle_id' => $request->vehicle_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json($reservation, 201);
    }

    public function getUserReservations(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized: No user authenticated'], 401);
        }

        $reservations = Reservation::where('user_id', $user->id)->with('vehicle')->get();

        return response()->json($reservations);
    }



    public function cancelReservation($id): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized: No user authenticated'], 401);
        }

        $reservation = Reservation::where('id', $id)->where('user_id', $user->id)->first();

        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found or you do not have permission to cancel this reservation.'], 404);
        }

        $reservation->delete();

        return response()->json(['message' => 'Reservation canceled successfully.'], 200);
    }
}
