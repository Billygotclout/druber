<template>
  <div class="pt-16">
    <form
      v-if="!waitingOnVerification"
      action="#"
      @submit.prevent="handleLogin"
    >
      <h1 class="text-3xl font-semibold mb-4">Enter your phone number</h1>
      <div
        class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left"
      >
        <div class="bg-white px-4 py-5 sm:p-6">
          <div>
            <input
              type="text"
              name="phone"
              id="phone"
              v-model="credentials.phone"
              v-maska
              data-maska="+#############"
              placeholder="1 (234) 567-8910"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-black focus:outline-none"
            />
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
          <button
            type="submit"
            class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-600 focus:outline-none"
          >
            Continue
          </button>
        </div>
      </div>
    </form>
    <form v-else @submit.prevent="handleVerification">
      <h1 class="text-3xl font-semibold mb-4">Verify your phone number</h1>
      <div
        class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left"
      >
        <div class="bg-white px-4 py-5 sm:p-6">
          <div>
            <input
              type="text"
              name="login_code"
              id="login_code"
              v-model="credentials.login_code"
              v-maska
              data-maska="######"
              placeholder="123456"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-black focus:outline-none"
            />
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
          <button
            type="submit"
            class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-gray-600 focus:outline-none"
          >
            Verify
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { vMaska } from "maska";
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
const router = useRouter();
const credentials = reactive({
  phone: null,
  login_code: null,
});
const waitingOnVerification = ref(false);
onMounted(() => {
  if (localStorage.getItem("token")) {
    router.push({
      name: "landing",
    });
  }
});

const handleLogin = async () => {
  try {
    console.log(credentials.phone);
    const response = await axios.post("http://127.0.0.1:8000/api/login", {
      phone: credentials.phone,
    });
    console.log(response.data);
    waitingOnVerification.value = true;
  } catch (error) {
    console.log(error);
    alert(error.response.data.message);
  }
};
const handleVerification = async () => {
  try {
    const response = await axios.post(
      "http://127.0.0.1:8000/api/login/verify",
      {
        phone: credentials.phone,
        login_code: credentials.login_code,
      }
    );
    console.log(response.data);
    localStorage.setItem("token", response.data);
    router.push({
      name: "landing",
    });
  } catch (error) {
    console.log(error);
    alert(error.response.data.message);
  }
};
</script>

<style lang="scss" scoped>
</style>