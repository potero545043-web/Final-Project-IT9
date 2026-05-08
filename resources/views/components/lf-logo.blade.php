<div class="lf-logo">
    <div class="logo-badge">
        <div class="book-icon">
            <div class="book-left"></div>
            <div class="book-right"></div>
            <div class="book-spine"></div>
        </div>
        <div class="lf-text">LF</div>
    </div>
</div>

<style>
.lf-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-badge {
    position: relative;
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.logo-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
}

.book-icon {
    position: relative;
    width: 40px;
    height: 35px;
    margin-bottom: 8px;
}

.book-left,
.book-right {
    position: absolute;
    width: 18px;
    height: 32px;
    background: white;
    top: 0;
    border-radius: 2px;
}

.book-left {
    left: 0;
    opacity: 0.85;
    transform: skewY(-2deg);
}

.book-right {
    right: 0;
    opacity: 0.65;
    transform: skewY(2deg);
}

.book-spine {
    position: absolute;
    width: 6px;
    height: 32px;
    background: white;
    left: 50%;
    top: 0;
    transform: translateX(-50%);
}

.lf-text {
    font-size: 32px;
    font-weight: 700;
    color: white;
    letter-spacing: -1px;
    font-family: 'Segoe UI', 'Arial', sans-serif;
}
</style>