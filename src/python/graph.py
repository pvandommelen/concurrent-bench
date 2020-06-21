from math import log, exp

import numpy as np
import matplotlib.pyplot as plt
import sys

from descent import gradient_descent

input_filepath = sys.argv[1]

timings = np.fromfile(input_filepath, dtype=float)
N = timings.size
x_range = np.arange(1, N + 1)
x = np.column_stack([
    np.ones(N),
    x_range,
    np.power(x_range, 2),
    np.log(x_range),
    x_range * np.log(x_range),
])
y = timings.reshape(N, 1)


def predict(x, theta):
    return np.matmul(x, theta.reshape([5, 1]))


def derivative(x, theta):
    return x


theta = gradient_descent(
    x,
    y,
    iterations=1000,
    predict=predict,
    derivative=derivative,
    limit_theta=lambda next_theta: np.maximum(next_theta, np.zeros(next_theta.shape))
)

contributions = [
    theta[0],
    theta[1] * N,
    theta[2] * N * N,
    theta[3] * log(N),
    theta[4] * N * log(N),
]
total_contribution = sum(contributions)


print('{0:>5.1f}%   {1: 1.2e}'.format(contributions[0] * 100 / total_contribution, theta[0]))
print('{0:>5.1f}%   {1: 1.2e} * N'.format(contributions[1] * 100 / total_contribution, theta[1]))
print('{0:>5.1f}%   {1: 1.2e} * N^2'.format(contributions[2] * 100 / total_contribution, theta[2]))
print('{0:>5.1f}%   {1: 1.2e} * log(N)'.format(contributions[3] * 100 / total_contribution, theta[3]))
print('{0:>5.1f}%   {1: 1.2e} * N * log(N)'.format(contributions[4] * 100 / total_contribution, theta[4]))
sys.stdout.flush()

max_y = max(timings)

plt.plot(x[:, 1], y[:, 0], 'bo')
plt.plot(x[:, 1], predict(x, theta), 'k--')
plt.ylim(0, max_y * 1.1)
plt.ylabel("Time (s)")
plt.xlim(0, N)
plt.xlabel("N")
plt.show()
