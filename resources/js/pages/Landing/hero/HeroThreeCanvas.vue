<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import * as THREE from 'three';

const containerRef = ref<HTMLDivElement | null>(null);

let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let renderer: THREE.WebGLRenderer | null = null;
let points: THREE.Points | null = null;
let gridMesh: THREE.LineSegments | null = null;
let animId: number | null = null;
let resizeObserver: ResizeObserver | null = null;

// Target mouse coordinates with smooth lerp
let targetX = 0;
let targetY = 0;
let currentX = 0;
let currentY = 0;

const onMouseMove = (e: MouseEvent) => {
    const { innerWidth, innerHeight } = window;
    targetX = (e.clientX / innerWidth - 0.5) * 2;
    targetY = (e.clientY / innerHeight - 0.5) * 2;
};

onMounted(() => {
    if (!containerRef.value) return;

    const width = containerRef.value.clientWidth || 600;
    const height = containerRef.value.clientHeight || 500;

    // 1. Scene & Camera
    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
    camera.position.z = 24;
    camera.position.y = -2;

    // 2. Renderer
    renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true,
        powerPreference: 'low-power',
    });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    containerRef.value.appendChild(renderer.domElement);

    // 3. Cyber Matrix Grid (Wireframe plane)
    const gridGeo = new THREE.PlaneGeometry(36, 28, 24, 18);
    const wireframeGeo = new THREE.WireframeGeometry(gridGeo);
    const gridMat = new THREE.LineBasicMaterial({
        color: 0xd97736,
        transparent: true,
        opacity: 0.12,
    });
    gridMesh = new THREE.LineSegments(wireframeGeo, gridMat);
    gridMesh.rotation.x = -Math.PI / 3.2;
    gridMesh.position.y = -6;
    scene.add(gridMesh);

    // 4. Floating Ambient Code Nodes (Particles)
    const particleCount = 140;
    const positions = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 3);

    const colorAmber = new THREE.Color(0xd97736);
    const colorCyan = new THREE.Color(0x38bdf8);
    const colorWhite = new THREE.Color(0xffffff);

    for (let i = 0; i < particleCount; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 32;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 24;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 16;

        const rand = Math.random();
        const chosenColor = rand > 0.6 ? colorAmber : rand > 0.25 ? colorCyan : colorWhite;
        colors[i * 3] = chosenColor.r;
        colors[i * 3 + 1] = chosenColor.g;
        colors[i * 3 + 2] = chosenColor.b;
    }

    const particleGeo = new THREE.BufferGeometry();
    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particleGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const particleMat = new THREE.PointsMaterial({
        size: 0.28,
        vertexColors: true,
        transparent: true,
        opacity: 0.45,
        blending: THREE.AdditiveBlending,
    });

    points = new THREE.Points(particleGeo, particleMat);
    scene.add(points);

    // 5. Animation Loop
    let clock = new THREE.Clock();

    const animate = () => {
        const elapsedTime = clock.getElapsedTime();

        // Smooth mouse lerp
        currentX += (targetX - currentX) * 0.05;
        currentY += (targetY - currentY) * 0.05;

        if (gridMesh) {
            gridMesh.rotation.z = Math.sin(elapsedTime * 0.15) * 0.04 + currentX * 0.08;
            gridMesh.position.y = -6 + Math.sin(elapsedTime * 0.4) * 0.3 - currentY * 0.6;
        }

        if (points) {
            points.rotation.y = elapsedTime * 0.03 + currentX * 0.1;
            points.rotation.x = Math.sin(elapsedTime * 0.05) * 0.05 + currentY * 0.08;
        }

        if (renderer && scene && camera) {
            renderer.render(scene, camera);
        }

        animId = requestAnimationFrame(animate);
    };

    animate();

    // 6. Event Listeners & ResizeObserver
    window.addEventListener('mousemove', onMouseMove, { passive: true });

    resizeObserver = new ResizeObserver((entries) => {
        if (!entries[0] || !camera || !renderer) return;
        const { width: newW, height: newH } = entries[0].contentRect;
        if (newW > 0 && newH > 0) {
            camera.aspect = newW / newH;
            camera.updateProjectionMatrix();
            renderer.setSize(newW, newH);
        }
    });

    resizeObserver.observe(containerRef.value);
});

onUnmounted(() => {
    if (animId) cancelAnimationFrame(animId);
    window.removeEventListener('mousemove', onMouseMove);
    if (resizeObserver && containerRef.value) {
        resizeObserver.unobserve(containerRef.value);
    }
    if (renderer) {
        renderer.dispose();
        if (renderer.domElement.parentElement) {
            renderer.domElement.parentElement.removeChild(renderer.domElement);
        }
    }
    scene = null;
    camera = null;
    renderer = null;
});
</script>

<template>
    <div
        ref="containerRef"
        class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden rounded-2xl opacity-60"
        aria-hidden="true"
    ></div>
</template>
