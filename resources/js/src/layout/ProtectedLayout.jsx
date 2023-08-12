import React, { useEffect } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import axios from '../services/axios';
import { useAuth } from '../context/AuthContext';
import AuthHeader from './AuthHeader';
export default function DefaultLayout() {
    const { user, setUser } = useAuth();

    // check if user is logged in or not from server
    useEffect(() => {
        (async () => {
            try {
                const resp = await axios.get('/user');
                if (resp.status === 200) {
                    setUser(resp1.data.data);
                }
            } catch (error) {
                // if (error.response.status === 401) {
                // 	localStorage.removeItem('user');
                // 	window.location.href = '/';
                // }
            }
        })();
    }, []);

    // if user is not logged in, redirect to login page
    if (!user) {
        return <Navigate to="/" />;
    }
    return (
        <>
            <main className="container flex justify-center flex-col items-center mt-10">
            <AuthHeader />
                <Outlet />
            </main>
        </>
    );
}