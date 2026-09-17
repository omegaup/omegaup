export function arrayBufferToBase64(buffer:ArrayBuffer) {
    const uint8Array = new Uint8Array(buffer);

    let binaryString = '';
    for (let i = 0; i < uint8Array.length; i++) {
        binaryString += String.fromCharCode(uint8Array[i]);
    }

    return btoa(binaryString);
}
export function base64ToBuffer(base64:string) {
    const binaryString = atob(base64);

    const buffer = new ArrayBuffer(binaryString.length);

    const uint8Array = new Uint8Array(buffer);

    for (let i = 0; i < binaryString.length; i++) {
        uint8Array[i] = binaryString.charCodeAt(i);
    }
    return uint8Array.buffer
}