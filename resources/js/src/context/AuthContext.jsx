import React, { createContext, useContext, useState } from 'react';
import axios from 'axios';

const AuthContent = createContext({
	user: null,
	setUser: () => {},
	csrfToken: () => {},
	token: null,
	setToken: () => {},
});

export const AuthProvider = ({ children }) => {
	const [user, _setUser] = useState(
		JSON.parse(localStorage.getItem('user')) || null
	);

	const [token, _setToken] = useState(
		localStorage.getItem('token') || null
	);

	// set user to local storage
	const setUser = (user) => {
		console.log('user: ', user);
		if (user) {
			console.log('set user to local storage');
			localStorage.setItem('user', JSON.stringify(user));
		} else {
			localStorage.removeItem('user');
		}
		_setUser(user);
	};

		// set user to local storage
		const setToken = (token) => {
			console.log('token: ', token);
			if (token) {
				console.log('set user to local storage');
				localStorage.setItem('token', token);
			} else {
				localStorage.removeItem('token');
			}
			_setToken(token);
		};
	
	// csrf token generation for guest methods
	const csrfToken = async () => {
		await axios.get('http://localhost:8000/sanctum/csrf-cookie');
		return true;
	};

	return (
		<AuthContent.Provider value={{ user, setUser, csrfToken, token, setToken }}>
			{children}
		</AuthContent.Provider>
	);
};

export const useAuth = () => {
	return useContext(AuthContent);
};