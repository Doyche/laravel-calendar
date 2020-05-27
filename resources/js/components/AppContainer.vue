<template>
    <v-app id="inspire">
        <v-content>
            <v-container
                class="fill-height"
                fluid
            >
                <v-row
                    align="center"
                    justify="center"
                >
                    <v-col
                        cols="12"
                        sm="8"
                        md="4"
                    >
                        <v-card class="elevation-12">
                            <v-toolbar
                                color="primary"
                                dark
                                flat
                            >
                                <v-toolbar-title>Insert Event</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text>
                                <v-alert
                                    v-if="reportMessage"
                                    type="info"
                                    dismissible
                                >
                                    {{reportMessage}}
                                </v-alert>

                                <v-form
                                    ref="form"
                                    v-model="eventForm"
                                    :lazy-validation="lazy"
                                >
                                    </v-text-field><v-text-field
                                        label="Name"
                                        name="name"
                                        prepend-icon="mdi-account"
                                        type="text"
                                        v-model="calendar.name"
                                        :rules="nameRules"
                                    ></v-text-field>

                                    <v-text-field
                                        label="Phone"
                                        name="phone"
                                        prepend-icon="mdi-cellphone-basic"
                                        type="text"
                                        v-model="calendar.phone"
                                        :rules="phoneRules"
                                        placeholder="0642939305"
                                    ></v-text-field>

                                    <v-text-field
                                        label="Email"
                                        name="email"
                                        prepend-icon="mdi-email"
                                        type="email"
                                        v-model="calendar.email"
                                        :rules="emailRules"
                                    ></v-text-field>

                                    <v-menu
                                        ref="menu1"
                                        v-model="time_menu"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        :return-value.sync="calendar.time"
                                        transition="scale-transition"
                                        offset-y
                                        max-width="290px"
                                        min-width="290px"
                                        prepend-icon="mdi-clock-time-four-outline"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="calendar.time"
                                                label="Time"
                                                prepend-icon="mdi-clock-time-four-outline"
                                                readonly
                                                v-on="on"
                                                :rules="timeRules"
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="time_menu"
                                            v-model="calendar.time"
                                            full-width
                                            @click:minute="$refs.menu1.save(calendar.time)"
                                        ></v-time-picker>
                                    </v-menu>

                                    <v-menu
                                        ref="menu2"
                                        v-model="date_menu"
                                        :close-on-content-click="false"
                                        :return-value.sync="calendar.date"
                                        transition="scale-transition"
                                        offset-y
                                        min-width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field
                                                v-model="calendar.date"
                                                label="Date"
                                                prepend-icon="mdi-calendar-range"
                                                readonly
                                                v-on="on"
                                                :rules="dateRules"
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker
                                            v-model="calendar.date"
                                            no-title
                                            scrollable
                                        >
                                            <v-spacer></v-spacer>
                                            <v-btn text color="primary" @click="date_menu = false">Cancel</v-btn>
                                            <v-btn text color="primary" @click="$refs.menu2.save(calendar.date)">OK</v-btn>
                                        </v-date-picker>
                                    </v-menu>


                                </v-form>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                    color="primary"
                                    @click="save"
                                >Insert</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    export default {
        name: "App",
        props: {
            source: String,
        },
        data: () => ({
            eventForm: true,
            lazy: true,
            nameRules: [
                value => !!value || 'Required.',
                value => (value || '').length <= 30 || 'Max 30 characters',
            ],
            phoneRules: [
                value => !!value || 'Required.',
                value => (value || '').length <= 30 || 'Max 30 characters',
                value => Number.isInteger(Number(value)) || "The value must be an integer number",
            ],
            emailRules: [
                value => !!value || 'Required.',
                value => (value || '').length <= 50 || 'Max 50 characters',
                value => {
                    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    return pattern.test(value) || 'Invalid e-mail.'
                },
            ],
            time_menu: false,
            timeRules: [
                value => !!value || 'Required.',
            ],
            date_menu: false,
            dateRules: [
                value => !!value || 'Required.',
            ],
            calendar: {
                name: '',
                phone: '',
                email: '',
                time: null,
                date: new Date().toISOString().substr(0, 10),
            },
            reportMessage: '',
        }),
        created() {
            this.initialize();
        },
        methods: {
            initialize() {
                axios.get('/api/calendar').then(response => {
                    console.log(response.data);
                });
            },

            validate() {
                this.$refs.form.validate();
            },

            save() {
                axios.post('/api/calendar/', this.calendar)
                    .then(response=>{
                        console.log(response.data);
                        this.reportMessage = response.data.message;
                    });
            },
        },
    }
</script>

<style scoped>

</style>
