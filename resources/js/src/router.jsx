import * as React from "react";

import { createBrowserRouter } from 'react-router-dom';
import ProtectedLayout from './layout/ProtectedLayout';
import GuestLayout from './layout/GuestLayout';

import Home from "./pages/Home";
import Login from "./pages/auth/Login";
import Signup from "./pages/auth/Signup";
import GoogleSeller from "./pages/GoogleSeller";
import AmazonSeller from "./pages/AmazonSeller";
import AmazonSellerMail from "./pages/AmazonSellerMail";
import GoogleSellerMail from "./pages/GoogleSellerMail";
import SettingPage from "./pages/SettingPage";
import AmazonScrapeHistory from "./pages/AmazonScrapeHistory";
import GoogleScrapeHistory from "./pages/GoogleScrapeHistory";
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
				path: '/amazon-seller-mail',
				element: <AmazonSellerMail />,
			},
			{
				path: '/google-seller-mail',
				element: <GoogleSellerMail />,
			},
			{
				path: '/settings',
				element: <SettingPage />,
			},
			{
				path: '/history-amazon',
				element: <AmazonScrapeHistory />,
			},
			{
				path: '/history-google',
				element: <GoogleScrapeHistory />,
			},
		],
	},
]);

export default router;