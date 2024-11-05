local HttpService = game:GetService("HttpService")

local function pingServer()
    local playerCount = game.Players.NumPlayers
    
    local postData = {
        id = 13,
        players = playerCount
    }
    
    local jsonData = HttpService:JSONEncode(postData)
    
    local success, response = pcall(function()
        return HttpService:PostAsync("https://www.novetusserverlist.com/api/ping_server", jsonData, Enum.HttpContentType.ApplicationJson)
    end)
    
    if success then
        print("Ping successful!")
        print("Response:", response)
    else
        print("Error:", response)
    end
end

while true do
    pingServer()
    wait(10)
end
