export default function Hero() {

    const handleClick = () => {
        alert("Welcome to PrimeHR 🚀");
    };

    return (
        <section className="text-center py-20">
            <h2 className="text-4xl font-bold mb-4">
                Welcome to PrimeHR 🚀
            </h2>

            <p className="text-gray-600 mb-6">
                Build your HR system with React + Laravel
            </p>

            <button
                onClick={handleClick}
                className="bg-blue-500 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-600 transition"
            >
                Get Started
            </button>
        </section>
    );
}
