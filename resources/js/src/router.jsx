import * as React from "react";

import { createBrowserRouter } from 'react-router-dom';
import ProtectedLayout from './layout/ProtectedLayout';
import GuestLayout from './layout/GuestLayout';

import Home from "./pages/Home";
import Login from "./pages/auth/Login";
import Signup from "./pages/auth/Signup";
import Dashboard from "./pages/Dashboard";
import GoogleSeller from "./pages/GoogleSeller";
import AmazonSeller from "./pages/AmazonSeller";
import AmazonSellerMail from "./pages/AmazonSellerMail";
import GoogleSellerMail from "./pages/GoogleSellerMail";

const router = createBrowserRouter([
	{
		path: '/',
		element: <GuestLayout />,
		children: [
			{
				path: '/',
				element: <Home />,
			},
            {
				path: '/login',
				element: <Login />,
			},
			{
				path: '/register',
				element: <Signup />,
			},
		],
	},
	{
		path: '/',
		element: <ProtectedLayout />,
		children: [
			{
				path: '/gseller',
				element: <GoogleSeller />,
			},
			{
				path: '/aseller',
				element: <AmazonSeller />,
			},
			{
				path: '/dashboard',
				element: <Dashboard />,
			},
			{
				path: '/amazon-seller-mail',
				element: <AmazonSellerMail />,
			},
			{
				path: '/google-seller-mail',
				element: <GoogleSellerMail />,
			},
		],
	},
]);

export default router;