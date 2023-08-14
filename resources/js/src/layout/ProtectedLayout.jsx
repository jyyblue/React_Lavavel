import React, { useEffect, useState } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import axios from '../services/axios';
import { useAuth } from '../context/AuthContext';
import AuthHeader from '../component/AuthHeader';
import Header from '../component/Header';
import Sidebar from '../component/Sidebar';

export default function ProtectedLayout() {
    const { user, setUser } = useAuth();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // check if user is logged in or not from server
    useEffect(() => {
        (async () => {
            try {
                const resp = await axios.get('/user');
                if (resp.status === 200) {
                    setUser(resp.data.data);
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
            <div className="dark:bg-boxdark-2 dark:text-bodydark">
                {/* <!-- ===== Page Wrapper Start ===== --> */}
                <div className="flex h-screen overflow-hidden">
                    {/* <!-- ===== Sidebar Start ===== --> */}
                    <Sidebar sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
                    {/* <!-- ===== Sidebar End ===== --> */}

                    {/* <!-- ===== Content Area Start ===== --> */}
                    <div className="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
                        {/* <!-- ===== Header Start ===== --> */}
                        <Header sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
                        {/* <!-- ===== Header End ===== --> */}

                        {/* <!-- ===== Main Content Start ===== --> */}
                        <main>
                            <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                                <Outlet />
                            </div>
                        </main>
                        {/* <!-- ===== Main Content End ===== --> */}
                    </div>
                    {/* <!-- ===== Content Area End ===== --> */}
                </div>
                {/* <!-- ===== Page Wrapper End ===== --> */}
            </div>
        </>
    );
}