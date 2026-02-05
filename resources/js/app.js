window.DashboardTime = {
    clock() {
        return {
            time: '',
            init() {
                this.update();
                setInterval(() => this.update(), 1000);
            },
            update() {
                this.time = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                });
            },
        };
    },
    countdown(targetTimestamp) {
        return {
            timeLeft: '',
            urgencyClass: 'text-emerald-300',
            init() {
                this.update();
                setInterval(() => this.update(), 1000);
            },
            update() {
                const now = Date.now();
                const target = targetTimestamp * 1000;
                const diff = target - now;

                if (diff <= 0) {
                    this.timeLeft = 'Time to go';
                    this.urgencyClass = 'text-red-400 font-semibold';
                    return;
                }

                const totalSeconds = Math.floor(diff / 1000);
                const totalMinutes = Math.floor(totalSeconds / 60);
                const minutes = totalMinutes;
                const seconds = totalSeconds % 60;
                const hours = Math.floor(totalMinutes / 60);

                if (minutes < 10) {
                    this.urgencyClass = 'text-red-400 font-semibold';
                } else if (minutes <= 30) {
                    this.urgencyClass = 'text-amber-300 font-semibold';
                } else {
                    this.urgencyClass = 'text-emerald-300';
                }

                if (hours > 0) {
                    this.timeLeft = `${hours}h ${totalMinutes % 60}m`;
                } else {
                    this.timeLeft = `${minutes}m ${seconds.toString().padStart(2, '0')}s`;
                }
            },
        };
    },
    eventCountdown(targetTimestamp) {
        return {
            timeLeft: '',
            init() {
                this.update();
                setInterval(() => this.update(), 60000);
            },
            update() {
                const now = Date.now();
                const target = targetTimestamp * 1000;
                const diff = target - now;

                if (diff <= 0) {
                    this.timeLeft = 'Now';
                    return;
                }

                const totalMinutes = Math.floor(diff / 60000);
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;

                if (hours > 0) {
                    this.timeLeft = `${hours}h ${minutes}m`;
                    return;
                }

                this.timeLeft = `${minutes}m`;
            },
        };
    },
};
