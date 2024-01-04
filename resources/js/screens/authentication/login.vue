<script type="text/ecmascript-6">
    import StylesMixin from './../../mixins/entriesStyles';
    import axios from 'axios';

    export default {
        mixins: [
            StylesMixin,
        ],

        data() {
            return {
                credentials: {
                    email: null,
                    password: null
                }
            }
        },

        methods: {

            async attemptLogin() {
                const response = await axios.post(Telescope.basePath + '/telescope-api/session', {
                    email: this.credentials.email,
                    password: this.credentials.password
                });

                if (response.status != 204) {
                    console.log('Login failed!');
                    return
                }

                this.$router.push('/requests');
            }
        }
    }
</script>

<template>
    <form class="card p-3" style="width: 400px" onsubmit="void()">
        <p class="m-2 text-center">
            Please login to continue
        </p>
        <hr />
        <div class="col mb-2">
            <label>Email:</label>
            <input class="form-control" type="text" v-model="credentials.email" />
        </div>
        <div class="col mb-4">
            <label>Password:</label>
            <input class="form-control" type="password" v-model="credentials.password" />
        </div>
        <div class="col text-right">
            <p class="float-left mb-0" style="margin-top: 0.34rem">
                <a href="javascript:;">Forgot password?</a>
            </p>
            <button type="reset" class="btn btn-default">
                Reset
            </button>
            <button type="button" class="btn btn-primary" v-on:click="attemptLogin()">
                Submit
            </button>
        </div>
    </form>
</template>
