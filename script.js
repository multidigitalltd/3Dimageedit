document.addEventListener("DOMContentLoaded", () => {
    console.log("Script loaded and DOM fully loaded.");

    const cameraStream = document.getElementById("camera-stream");
    const arElement = document.getElementById("ar-element");
    const captureButton = document.getElementById("capture-photo");
    const photoCanvas = document.getElementById("photo-canvas");
    const canvasContext = photoCanvas.getContext("2d");

    // הפעלת המצלמה
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices
            .getUserMedia({ video: true })
            .then((stream) => {
                cameraStream.srcObject = stream;
                cameraStream.play();
                console.log("Camera started successfully.");
            })
            .catch((err) => {
                console.error("Failed to access the camera:", err);
            });
    } else {
        alert("Camera not supported in this browser.");
    }

    // הפיכת אלמנט ה-AR לגריר
    arElement.addEventListener("mousedown", (event) => {
        let isDragging = false;
        const offset = { x: 0, y: 0 };

        const startDrag = (e) => {
            isDragging = true;
            offset.x = e.clientX - arElement.offsetLeft;
            offset.y = e.clientY - arElement.offsetTop;
        };

        const duringDrag = (e) => {
            if (!isDragging) return;
            arElement.style.left = `${e.clientX - offset.x}px`;
            arElement.style.top = `${e.clientY - offset.y}px`;
        };

        const endDrag = () => {
            isDragging = false;
        };

        document.addEventListener("mousemove", duringDrag);
        document.addEventListener("mouseup", endDrag);
        startDrag(event);
    });

    // צילום ושמירת תמונה
    captureButton.addEventListener("click", async () => {
        try {
            const videoWidth = cameraStream.videoWidth;
            const videoHeight = cameraStream.videoHeight;

            if (!videoWidth || !videoHeight) {
                console.error("Camera dimensions not available.");
                return;
            }

            photoCanvas.width = videoWidth;
            photoCanvas.height = videoHeight;

            // צייר את מצלמת הווידאו על הקנבס
            canvasContext.drawImage(cameraStream, 0, 0, videoWidth, videoHeight);

            // צייר את המודל התלת-ממדי על הקנבס
            if (arElement.toBlob) {
                const arBlob = await arElement.toBlob();
                const arImage = await createImageBitmap(arBlob);

                canvasContext.drawImage(
                    arImage,
                    arElement.offsetLeft,
                    arElement.offsetTop,
                    arElement.clientWidth,
                    arElement.clientHeight
                );
            } else {
                console.error("toBlob is not supported for AR element.");
            }

            // המרת הקנבס לתמונה
            const photoData = photoCanvas.toDataURL("image/png");

            // שליחת התמונה לשרת
            fetch(ajax_object.ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=save_photo&photo=${encodeURIComponent(photoData)}`,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        console.log("Photo saved successfully:", data);
                        alert("Photo saved successfully!");
                    } else {
                        console.error("Error saving photo:", data);
                    }
                })
                .catch((err) => {
                    console.error("AJAX error:", err);
                });
        } catch (err) {
            console.error("Error during capture:", err);
        }
    });
});
