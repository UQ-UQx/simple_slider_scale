import 'bootstrap/dist/css/bootstrap.css'

import React from "react"
import ReactDOM from "react-dom"

import axios from "axios"
import Container from "./components/Container"

const app = document.getElementById('app');

axios.get('../public/api/api.php', {
    params: {
        action: "getUserState",
        data:{
            user_id: $LTI_userID,
            lti_id: $LTI_resourceID
        }
    }
})
.then(function (response) {
    var serverState = JSON.parse(response.data.state)
    loadApp(serverState)
})
.catch(function (error) {
    loadApp(null)
});

function loadApp(state){
    ReactDOM.render(<Container appState={state}/>, app);
}