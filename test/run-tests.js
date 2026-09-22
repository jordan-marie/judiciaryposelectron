const assert = require('assert');
const serialManager = require('../main/serial/serialManager');
const ocrPipeline = require('../main/camera/ocrPipeline');

async function runTests() {
  console.log('--- Testing Serial Manager Sanitizer & Parser ---');

  // Test case 1: Standard indicator frame with STX/ETX and KG
  const sample1 = '\x02ST,GS,+012450kg\x03\r\n';
  const parsed1 = serialManager.sanitizeAndParse(sample1);
  assert.strictEqual(parsed1.weight, 12450);
  assert.strictEqual(parsed1.unit, 'kg');
  assert.strictEqual(parsed1.isStable, true);
  assert.strictEqual(parsed1.valid, true);
  console.log('✓ Test 1 passed: Standard serial frame parsing');

  // Test case 2: Unstable indicator reading
  const sample2 = '\x02US,GS,+00850.5lbs\x03\r\n';
  const parsed2 = serialManager.sanitizeAndParse(sample2);
  assert.strictEqual(parsed2.weight, 850.5);
  assert.strictEqual(parsed2.unit, 'lb');
  assert.strictEqual(parsed2.isStable, false);
  assert.strictEqual(parsed2.valid, true);
  console.log('✓ Test 2 passed: Unstable reading and lbs parsing');

  // Test case 3: Unparseable noise string
  const sample3 = '\x00\x01\xFFGARBAGE_NOISE\x03';
  const parsed3 = serialManager.sanitizeAndParse(sample3);
  assert.strictEqual(parsed3.weight, null);
  assert.strictEqual(parsed3.valid, false);
  console.log('✓ Test 3 passed: Noise string handling');

  console.log('--- Testing Serial Manager Mock Mode ---');
  await new Promise((resolve) => {
    let count = 0;
    const weightHandler = (data) => {
      count++;
      assert.ok(typeof data.weight === 'number');
      if (count >= 2) {
        serialManager.off('weight', weightHandler);
        serialManager.disconnect();
        console.log('✓ Test 4 passed: Mock mode weight emission');
        resolve();
      }
    };
    serialManager.on('weight', weightHandler);
    serialManager.connect({ mock: true });
  });

  console.log('--- Testing OCR Pipeline Threshold Handling ---');
  ocrPipeline.setConfidenceThreshold(80);
  assert.strictEqual(ocrPipeline.confidenceThreshold, 80);

  // Test case 5: Empty image input handling
  const nullResult = await ocrPipeline.processFrame(null);
  assert.strictEqual(nullResult.status, 'PLATE_NOT_DETECTED');
  assert.strictEqual(nullResult.plateText, null);
  console.log('✓ Test 5 passed: Null image fallback to PLATE_NOT_DETECTED');

  console.log('\nAll tests completed successfully!');
}

runTests().catch((err) => {
  console.error('Test Suite Failed:', err);
  process.exit(1);
});
