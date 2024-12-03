export class LayerManager {
    constructor(map) {
        this.map = map;
        this.wmsLayer = null;
        this.laHienLayer = null;
        this.thaiNguyenPolygons = [];
    }

    // Khởi tạo WMS Layer
    initWMSLayer() {
        this.wmsLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'air_cement'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'aqiWMS'
        });

        this.laHienLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'la_hien_plume'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'laHienWMS'
        });

        this.map.overlayMapTypes.push(this.wmsLayer);
        this.map.overlayMapTypes.push(this.laHienLayer);
    }

    // Lấy URL cho WMS tile
    getWMSTileUrl(coord, zoom, layer) {
        const proj = this.map.getProjection();
        const zfactor = Math.pow(2, zoom);
        const top = proj.fromPointToLatLng(
            new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor)
        );
        const bot = proj.fromPointToLatLng(
            new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor)
        );
        const bbox = [top.lng(), bot.lat(), bot.lng(), top.lat()].join(',');

        return `http://geoserver.tuaf.edu.vn/mt_thainguyen/wms?service=WMS&version=1.1.0&request=GetMap&layers=mt_thainguyen:${layer}&styles=&bbox=${bbox}&width=256&height=256&srs=EPSG:4326&format=image/png&transparent=true`;
    }

    // Thêm ranh giới Thái Nguyên
    addThaiNguyenBoundaries(boundaries) {
        // Xóa các polygon cũ nếu có
        this.clearThaiNguyenBoundaries();

        boundaries.forEach(area => {
            // Kiểm tra dữ liệu geometry
            if (!area.geometry?.coordinates?.[0]?.[0]) {
                console.error('Invalid geometry data for area:', area);
                return;
            }

            const coordinates = area.geometry.coordinates[0][0].map(coord => ({
                lat: coord[1],
                lng: coord[0]
            }));

            const polygon = new google.maps.Polygon({
                paths: coordinates,
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.1,
                map: this.map,
                visible: true // Mặc định hiển thị
            });

            this.setupPolygonListeners(polygon);
            this.thaiNguyenPolygons.push({
                id: area.id,
                polygon: polygon
            });
        });
    }

    // Xóa tất cả ranh giới
    clearThaiNguyenBoundaries() {
        this.thaiNguyenPolygons.forEach(item => {
            if (item.polygon) {
                item.polygon.setMap(null);
            }
        });
        this.thaiNguyenPolygons = [];
    }

    // Thiết lập sự kiện cho polygon
    setupPolygonListeners(polygon) {
        polygon.addListener("mouseover", () => {
            polygon.setOptions({ fillOpacity: 0.3 });
        });

        polygon.addListener("mouseout", () => {
            polygon.setOptions({ fillOpacity: 0.1 });
        });
    }

    // Làm mới layer WMS
    refreshWMSLayers() {
        const overlays = this.map.overlayMapTypes.getArray();
        overlays.forEach((overlay, i) => {
            if (overlay?.name === 'aqiWMS') {
                this.map.overlayMapTypes.removeAt(i);
                this.map.overlayMapTypes.push(this.wmsLayer);
            }
            if (overlay?.name === 'laHienWMS') {
                this.map.overlayMapTypes.removeAt(i);
                this.map.overlayMapTypes.push(this.laHienLayer);
            }
        });
    }
}