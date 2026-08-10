self.onmessage = function(event) {
    const file = event.data;
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const kmlText = e.target.result;
        const parser = new DOMParser();
        const xml = parser.parseFromString(kmlText, "text/xml");
        const geojson = toGeoJSON.kml(xml);
        
        self.postMessage(geojson);  // ส่งกลับข้อมูล GeoJSON ไปที่ main thread
    };

    reader.readAsText(file);  // อ่านไฟล์ KML
};
