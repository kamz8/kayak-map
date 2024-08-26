export default {
    methods: {
        formatDuration(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        },
        formatAvgDuration(length, AVGSpeed = 4.5) {
            // const avgSpeed = 3.4; // średnia prędkość w km/h
            const timeInHours = (length /1000) / AVGSpeed;
            const minutes = Math.round(timeInHours * 60);
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        },
        formatTrailLength(length) {
            return `${(length / 1000).toFixed(1)} km`;
        },
    }
}
