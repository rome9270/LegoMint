from welcome_func import welcome
from points_func import get_points
from slope_func import calculate_slope
from intercept_func import calculate_intercept

def main():
    welcome()
    x1, y1, x2, y2 = get_points()
    m = calculate_slope(x1, y1, x2, y2)
    t = calculate_intercept(x1, y1, m)

    if m is not None and t is not None:
        if m == 0 and t == 0:
            print("f(x) = y = 0")
        elif m == 0:
            print(f"f(x) = y = {t}")
        elif t == 0:
            print(f"f(x) = y = {m} * x")
        elif t > 0:
            print(f"f(x) = y = {m} * x + {t}")
        else:
            print(f"f(x) = y = {m} * x - {abs(t)}")

if __name__ == "__main__":
    main()
