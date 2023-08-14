import Axios from 'axios';
import { useAuth } from '../context/AuthContext';

const token = localStorage.getItem('token', '')
const axios = Axios.create({
	baseURL: "http://localhost:8000/api",
	// withCredentials: true,
	headers: {
		"Content-Type": "application/json",
		"Accept": "application/json",
		"Authorization": "Bearer " + token,
	},
});

export default axios;