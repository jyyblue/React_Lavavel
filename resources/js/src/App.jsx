import React from "react";
import { createRoot } from "react-dom/client";
import { Route, BrowserRouter as Router, Routes } from "react-router-dom";
import router from './router';
import { RouterProvider } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';

function App() {
    return (
        // <React.StrictMode>
            <AuthProvider>
                <RouterProvider router={router} />
            </AuthProvider>
        // </React.StrictMode>
    );
}
export default App;

const rootElement = document.getElementById("app");
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<App />);
}
