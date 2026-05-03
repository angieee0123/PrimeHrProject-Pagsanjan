import Navbar from './components/Navbar';
import Hero from './components/Hero';
import Card from './components/Card';
import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

export default function App() {
    return (
        <div className="bg-gray-100 min-h-screen">

            <Navbar />
            <Hero />

            <section className="max-w-6xl mx-auto py-10 px-4 grid md:grid-cols-3 gap-6">
                <Card title="Employees" description="Manage employee records" />
                <Card title="Attendance" description="Track attendance" />
                <Card title="Reports" description="Generate reports" />
            </section>

        </div>
    );
}
ReactDOM.createRoot(document.getElementById('app')).render(<App />);
