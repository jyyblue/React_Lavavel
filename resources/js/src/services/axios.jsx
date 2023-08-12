import Axios from 'axios';
const token = JSON.parse(localStorage.getItem('token'));

const axios = Axios.create({
	baseURL: "http://localhost:8000/api",
	withCredentials: true,
	headers: {
		"Content-Type": "application/json",
		"Accept": "application/json",
        // 'Authorization': 'Bearer '+token,
	},
});

export default axios;