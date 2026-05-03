export default function Card({ title, description }) {
    return (
        <div className="bg-white p-6 rounded-xl shadow hover:scale-105 transition">
            <h3 className="font-bold text-lg mb-2">{title}</h3>
            <p className="text-gray-600">{description}</p>
        </div>
    );
}
