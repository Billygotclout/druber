<template>
  <div class="pt-16">
    <h1 class="text-3xl font-semibold mb-4">{{ title }}</h1>
    <div v-if="!trip.id" class="mt-8 flex justify-center">
      <Loader />
    </div>
    <div
      v-else
      class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left"
    >
      <div class="bg-white px-4 py-5 sm:p-6">
        <div>
          <GMapMap
            :zoom="14"
            :center="trip.destination"
            ref="gMap"
            style="width: 100%; height: 256px"
          ></GMapMap>
        </div>
        <div class="mt-2">
          <p class="text-xl">
            Going to <strong>{{ trip.destination_name }}</strong>
          </p>
        </div>
      </div>
      <div class="flex justify-between bg-gray-50 px-4 py-3 text-right sm:px-6">
        <button
          @click="handleDeclineTrip"
          class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-600 focus:outline-none"
        >
          Decline
        </button>
        <button
          @click="handleAcceptTrip"
          class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-600 focus:outline-none"
        >
          Accept
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import Loader from "../components/Loader.vue";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { useLocationStore } from "../stores/location";
import { useTripStore } from "../stores/trip";
import http from "../helpers/http";
import { useRouter } from "vue-router";
const title = ref("Waiting for Ride Request");
const trip = useTripStore();
const gMap = ref(null);
const router = useRouter();
const location = useLocationStore();
const handleDeclineTrip = () => {
  trip.reset();
  title.value = "Waiting for Ride Request";
};

const handleAcceptTrip = async () => {
  try {
    const response = await http().post(`/api/trip/${trip.id}/accept`, {
      driver_location: location.current.geometry,
    });
    if (response.data) {
      location.$patch({
        destination: {
          name: "Passenger",
          geometry: response.data.origin,
        },
      });
      router.push({ name: "driving" });
    }
  } catch (error) {
    console.log(error);
  }
};
onMounted(async () => {
  await location.updateCurrentLocation();
  console.log("mounted");
  try {
    let echo = new Echo({
      broadcaster: "pusher",
      key: "mykey",
      cluster: "mt1",
      wsHost: "127.0.0.1",
      wsPort: 6001,
      forceTLS: false,
      disableStats: true,
      enabledTransports: ["ws", "wss"],
    });
    echo.channel("drivers").listen("TripCreated", (e) => {
      title.value = "Ride Requested";
      trip.$patch(e.trip);
      console.log(e);
      setTimeout(initMapDirections, 1000);
    });
  } catch (error) {
    console.log(error);
  }
});
const initMapDirections = () => {
  gMap.value.$mapPromise.then((mapObject) => {
    let originPoint = new google.maps.LatLng(trip.origin),
      destinationPoint = new google.maps.LatLng(trip.destination),
      directionsService = new google.maps.DirectionsService(),
      directionsDisplay = new google.maps.DirectionsRenderer({
        map: mapObject,
      });

    directionsService.route(
      {
        origin: originPoint,
        destination: destinationPoint,
        avoidTolls: false,
        avoidHighways: false,
        travelMode: google.maps.TravelMode.DRIVING,
      },
      (res, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
          directionsDisplay.setDirections(res);
        } else {
          console.error(status);
        }
      }
    );
  });
};
</script>

<style lang="scss" scoped>
</style>