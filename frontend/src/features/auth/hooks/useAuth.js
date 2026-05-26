import {ApiService} from "@/api/services.js";
import {AuthContext} from "@/context/AuthContext.jsx";
import {useContext} from "react";
import {setAccessToken} from "@/api/axios.js";
import { useNavigate } from 'react-router-dom';

export const useAuth = () => {
    const {authState, login, setPinVerfied} = useContext(AuthContext);

    async function loginUser (credentials){
       try{
           const response = await ApiService.login(credentials);
           setAccessToken(response.data.token);
           login();
           navigate('/dashboard');
       }catch (error){
           console.error("Exception occured" , error, response.status);
       }
    }

    return {
        authState
    }
}