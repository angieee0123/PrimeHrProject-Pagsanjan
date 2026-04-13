export default function Navbar() {
    return (
        <nav className="bg-white shadow-md">
            <div className="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
                <h1 className="text-xl font-bold text-blue-600">PrimeHR</h1>
                <div className="space-x-4">
                    <a href="#" className="hover:text-blue-500">Home</a>
                    <a href="#" className="hover:text-blue-500">About</a>
                    <a href="#" className="hover:text-blue-500">Contact</a>
                </div>
            </div>
        </nav>
    );
}
